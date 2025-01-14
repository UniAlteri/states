<?php

/*
 * States.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/libraries/states Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\States\State;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use SensitiveParameter;
use Teknoo\States\Proxy\ProxyInterface;

use function is_subclass_of;

/**
 * Default implementation of the state interface, representing states entities in stated class.
 * A trait implementation has been chosen to allow developer to write theirs owns factory, extendable from any class.
 *
 * Objects implementing this interface must find, bind and execute closure via the method executeClosure() for the
 * required method. (Rebind must use `\Closure::call()` to rebind `static`, `self` and `$this` or `rebindTo()`).
 *
 * Objects must follow instruction passed to `executeClosure()` and manage the visibility of the method and not allow
 * executing a private method from an outside call.
 *
 * Result must be injected to the proxy by using the callback passed to `executeClosure()`. It's allowed to execute a
 * method without inject the result into the proxy instance to allow developers to call several methods. But you can
 * only inject one result by call. (Several implementations available at a same time is forbidden by the proxy
 * interface).
 *
 * Static method are not managed (a class can not have a state, only it's instance).
 *
 * State's methods are not directly executed. They are a builder to create the closure, they must
 * return them self the closure. So, writing state differs from previous version, example :
 *
 *      <method visibility> function <method name>(): \Closure
 *      {
 *          return function($arg1, $arg2) {
 *              //your code
 *          };
 *      }
 *      method visibility : public/protected/private, visibility used in the proxy instance, for your method
 *      method name: a string, used in the proxy, for your method.
 *
 * Contrary to previous versions of this library, methods of states's object are not directly converted into a \Closure.
 * Since 7.0, `\Closure` created from the Reflection Api can not be bound to an another class (only rebind of $this
 * is permitted), so the feature `\Closure::call()` was not usable. Since 7.1, rebind $this for this special closure
 * is also forbidden.
 *
 * @api
 *
 * @see StateInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @mixin StateInterface
 */
trait StateTrait
{
    /**
     * Reflection class object of this state to extract closures and description.
     * @var ReflectionClass<object>
     */
    private ?ReflectionClass $reflectionClass = null;

    /**
     * Reflections methods of this state to extract description and closures.
     *
     * @var ReflectionMethod[]|bool[]
     */
    private array $reflectionsMethods = [];

    /**
     * List of closures already extracted and set into Injection Closure Container.
     *
     * @var Closure[]
     */
    private array $closuresObjects = [];

    /**
     * @var array<string, array<int|string, array<int|string, bool>>>
     */
    private array $visibilityCache = [];

    public function __construct(
        private bool $privateModeStatus,
        private string $statedClassName,
    ) {
    }

    /**
     * To build the ReflectionClass for the current object.
     *
     * @api
     *
     * @return ReflectionClass<object>
     * @throws ReflectionException
     */
    private function getReflectionClass(): ReflectionClass
    {
        if (null === $this->reflectionClass) {
            $this->reflectionClass = new ReflectionClass($this::class);
        }

        return $this->reflectionClass;
    }

    /**
     * To check if the caller method can be accessible by the method caller :
     * The called method is protected or public (skip to next test)
     * The private mode is disable for this state (state is not defined is a parent class)
     * The caller method is in the same stated class that the called method.
     */
    private function checkVisibilityPrivate(string &$methodName, string &$statedClassOrigin): bool
    {
        if (
            true === $this->privateModeStatus
            && $statedClassOrigin !== $this->statedClassName
            && $this->reflectionsMethods[$methodName] instanceof ReflectionMethod
            && true === $this->reflectionsMethods[$methodName]->isPrivate()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Can not access to private methods, only public and protected.
     */
    private function checkVisibilityProtected(string &$methodName, string &$statedClassOrigin): bool
    {
        //It's a public or protected method, do like if there is no method
        return $this->reflectionsMethods[$methodName] instanceof ReflectionMethod
            && false === $this->reflectionsMethods[$methodName]->isPrivate()
            && !empty($statedClassOrigin)
            && (
                $statedClassOrigin === $this->statedClassName
                || is_subclass_of($statedClassOrigin, $this->statedClassName)
            );
    }

    /**
     * Can not access to protect and private method.
     */
    private function checkVisibilityPublic(string &$methodName): bool
    {
        //It's a public method, do like if there is no method
        return $this->reflectionsMethods[$methodName] instanceof ReflectionMethod
            && true === $this->reflectionsMethods[$methodName]->isPublic();
    }

    /**
     * To check if the method is available in the required scope (check from the visibility of the method) :
     *  Public method : Method always available
     *  Protected method : Method available only for this stated class's methods (method present in this state
     *      or another state) and its children
     *  Private method : Method available only for this stated class's method (method present in this state or
     *      another state) and not for its children.
     *
     * @throws Exception\InvalidArgument
     */
    private function checkVisibility(
        string &$methodName,
        Visibility $scope,
        string &$statedClassOrigin
    ): bool {
        //Check visibility scope
        return $this->visibilityCache[$scope->value][$statedClassOrigin][$methodName] ??= match ($scope) {
            Visibility::Private => $this->checkVisibilityPrivate($methodName, $statedClassOrigin),
            Visibility::Protected => $this->checkVisibilityProtected($methodName, $statedClassOrigin),
            Visibility::Public => $this->checkVisibilityPublic($methodName),
        };
    }

    /**
     * To return the description of a method to configure the behavior of the proxy. Return also description of private
     * methods : getMethodDescription() does not check if the caller is allowed to call the required method.
     *
     * getMethodDescription() ignores static method, because there are incompatible with the stated behavior :
     * State can be only applied on instances entities like object,
     * and not on static entities which by nature have no states
     *
     * @api
     *
     * @throws Exception\MethodNotImplemented is the method does not exist
     * @throws ReflectionException
     */
    private function loadMethodDescription(string &$methodName): bool
    {
        if (isset($this->reflectionsMethods[$methodName])) {
            return $this->reflectionsMethods[$methodName] instanceof ReflectionMethod;
        }

        $thisReflectionClass = $this->getReflectionClass();
        if (!$thisReflectionClass->hasMethod($methodName)) {
            $this->reflectionsMethods[$methodName] = false;

            return false;
        }

        //Load Reflection Method if it is not already done
        $methodDescription = $thisReflectionClass->getMethod($methodName);
        if (false !== $methodDescription->isStatic()) {
            $this->reflectionsMethods[$methodName] = false;

            return false;
        }

        $this->reflectionsMethods[$methodName] = $methodDescription;

        return true;
    }

    /**
     * To return a closure of the required method to use in the proxy, in the required scope (check from the visibility
     * of the method) :
     *  Public method : Method always available
     *  Protected method : Method available only for this stated class's methods (method present in this state or
     *      another state) and its children
     *  Private method : Method available only for this stated class's method (method present in this state or another
     *      state) and not for its children.
     *
     * @throws Exception\MethodNotImplemented is the method does not exist
     * @throws ReflectionException
     */
    private function getClosure(
        string &$methodName
    ): ?Closure {
        if (isset($this->closuresObjects[$methodName])) {
            return $this->closuresObjects[$methodName];
        }

        //Check if the method exist and prepare description for checkVisibility methods
        if (!$this->loadMethodDescription($methodName)) {
            return null;
        }

        //Call directly the closure builder, more efficient
        $closure = $this->{$methodName}();

        if (!$closure instanceof Closure) {
            throw new Exception\MethodNotImplemented(
                "Method '$methodName' is not a valid Closure"
            );
        }

        $this->closuresObjects[$methodName] = $closure;

        return $this->closuresObjects[$methodName];
    }

    /**
     * @throws ReflectionException
     */
    public function executeClosure(
        ProxyInterface $object,
        string &$methodName,
        #[SensitiveParameter] array &$arguments,
        Visibility $requiredScope,
        string &$statedClassOrigin,
        callable &$returnCallback
    ): StateInterface {
        $closure = $this->getClosure($methodName);

        //Check visibility scope
        if (
            !$closure instanceof Closure
            || false === $this->checkVisibility($methodName, $requiredScope, $statedClassOrigin)
        ) {
            return $this;
        }

        if (true === $this->privateModeStatus) {
            $closure = $closure->bindTo($object, $this->statedClassName);
            if ($closure instanceof Closure) {
                $returnValue = $closure(...$arguments);
                $returnCallback($returnValue);
            }
        } else {
            $returnValue = $closure->call($object, ...$arguments);
            $returnCallback($returnValue);
        }

        return $this;
    }
}
