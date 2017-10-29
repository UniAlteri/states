<?php

declare(strict_types=1);

/**
 * States.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\States\Proxy;

use Teknoo\States\State\StateInterface;

/**
 * Trait ProxyTrait
 * Implementation of the proxy class in stated class. It is used in this library to create stated class instance.
 *
 * The proxy, by default, redirect all calls, of non defined methods in the proxy, to enabled states.
 * $this, static and self keywords in all methods the stated class instance (aka in proxy's method and states' methods)
 * represent the proxy instance.
 *
 * The proxy class is mandatory. States 3.0 has no factories or no loader : proxies embedded directly theirs states'
 * configurations.
 *
 * States can be overload by children of a stated class : The overloading uses only the non qualified name.
 *
 * Since 3.0, states's methods are a builder, returning a real closure to use. The state must not use
 * the Reflection API to extract the closure (Closure from Reflection are not bindable on a new scope since 7.1).
 * States can be also an anonymous class, it's name must be defined by an interface, implementing by this state.
 *
 * @see ProxyInterface
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @mixin ProxyInterface
 */
trait ProxyTrait
{
    /**
     * List of currently enabled states in this proxy.
     *
     * @var StateInterface[]
     */
    private $activesStates = [];

    /**
     * List of available states for this stated class instance.
     *
     * @var StateInterface[]
     */
    private $states = [];

    /**
     * To register the list original classes names (mother class name if needed) for each state
     *
     * @var string[]|array
     */
    private $classesByStates = [];

    /**
     * To keep the list of full qualified state in parent classes to allow enable overload/redefined state with
     * original full qualified state name.
     *
     * @var array|string[]
     */
    private $statesAliasesList = [];

    /**
     * Stack to know the caller full qualified stated class when an internal method call a parent method to forbid
     * private method access.
     *
     * @var string[]|\SplStack
     */
    private $callerStatedClassesStack;

    /**
     * List all states's classes available in this state. It's not mandatory to redefine states of parent's class,
     * They are automatically loaded by the proxy. Warning, if you redeclare a state of a parent's class with its full
     * qualified class name, you can access to its private method: this declaration overloads the parent's state and
     * the state is owned by the child class.
     *
     * Example:
     * return [
     *  myFirstState::class,
     *  mySecondState::class
     * ];
     *
     * @internal
     * @return array|string[]
     */
    abstract protected static function statesListDeclaration(): array;

    /**
     * To instantiate a state class defined in this proxy. Is a state have a same non fullqualified class name of
     * a previous loaded state (defined in previously in this class or in children) it's skipped.
     *
     * @param array  $statesList
     * @param bool   $enablePrivateMode
     * @param string $selfClassName
     * @param array  &$loadedStatesList
     */
    private function initializeStates(
        array $statesList,
        bool $enablePrivateMode,
        string $selfClassName,
        array &$loadedStatesList
    ) {
        foreach ($statesList as $stateClassName) {
            //Extract non qualified class name and check if this state is not already loaded
            $shortStateName = \ltrim(\substr($stateClassName, (int) \strrpos($stateClassName, '\\')), '\\');
            if (isset($loadedStatesList[$shortStateName])) {
                $this->statesAliasesList[$stateClassName] = $loadedStatesList[$shortStateName];
                $this->classesByStates[$stateClassName] = $selfClassName;

                continue;
            }

            //Register it
            $loadedStatesList[$shortStateName] = $stateClassName;

            //Load and Register
            $this->registerState(
                $stateClassName,
                new $stateClassName($enablePrivateMode, $selfClassName),
                $selfClassName
            );

            //If the state is the default
            if ($shortStateName == ProxyInterface::DEFAULT_STATE_NAME) {
                $this->enableState($stateClassName);
            }
        }
    }

    /**
     * To initialize the proxy instance with all declared states. This method fetch all states defined for this class,
     * (states returned by `statesListDeclaration()`), but checks also parent's states by calling theirs static methods
     * `statesListDeclaration`.
     *
     * @return ProxyInterface
     */
    private function loadStates(): ProxyInterface
    {
        $currentClassName = static::class;
        $loadedStatesList = [];

        //Private mode is only enable for states directly defined in this stated class.
        $this->initializeStates(
            static::statesListDeclaration(),
            false,
            $currentClassName,
            $loadedStatesList
        );

        $parentClassName = \get_class($this);
        do {
            $parentClassName = \get_parent_class($parentClassName);
            if (\is_string($parentClassName) && \class_exists($parentClassName)
                    && \is_subclass_of($parentClassName, ProxyInterface::class)) {
                //Private mode is disable for states directly defined in parent class.
                /**
                 * @var ProxyInterface|ProxyTrait $parentClassName
                 */
                $statesList = $parentClassName::statesListDeclaration();
                $this->initializeStates($statesList, true, $parentClassName, $loadedStatesList);
            }
        } while (false !== $parentClassName);

        return $this;
    }

    /**
     * To get the class name of the caller according to scope visibility.
     *
     * @return string
     */
    private function getCallerStatedClassName(): string
    {
        if (true !== $this->callerStatedClassesStack->isEmpty()) {
            return $this->callerStatedClassesStack->top();
        }

        return '';
    }

    /**
     * To push in the caller stated classes name stack
     * the class of the current object.
     *
     * @param StateInterface $state
     *
     * @return ProxyInterface
     */
    private function pushCallerStatedClassName(StateInterface $state): ProxyInterface
    {
        $stateClass = \get_class($state);

        if (!isset($this->classesByStates[$stateClass])) {
            throw new \RuntimeException("Error, no original class name defined for $stateClass");
        }

        $this->callerStatedClassesStack->push($this->classesByStates[$stateClass]);

        return $this;
    }

    /**
     * To pop the current caller in the stated class name stack.
     *
     * @return ProxyInterface
     */
    private function popCallerStatedClassName(): ProxyInterface
    {
        if (false === $this->callerStatedClassesStack->isEmpty()) {
            $this->callerStatedClassesStack->pop();
        }

        return $this;
    }

    /**
     * Prepare the execution's context and execute a method in a state passed in args with the closure.
     *
     * @param StateInterface $state
     * @param string         $methodName
     * @param array          $arguments
     * @param string         $scopeVisibility self::VISIBILITY_PUBLIC
     *                                        self::VISIBILITY_PROTECTED
     *                                        self::VISIBILITY_PRIVATE
     * @param callable $callback
     *
     * @return self|ProxyInterface
     *
     * @throws \Throwable
     */
    private function callMethod(
        StateInterface $state,
        string $methodName,
        array &$arguments,
        string $scopeVisibility,
        callable $callback
    ) : ProxyInterface {
        $callerStatedClass = $this->getCallerStatedClassName();
        $this->pushCallerStatedClassName($state);

        //Call it
        try {
            $state->executeClosure($this, $methodName, $arguments, $scopeVisibility, $callerStatedClass, $callback);
        } catch (\Throwable $e) {
            //Restore stated class name stack
            $this->popCallerStatedClassName();

            throw $e;
        }

        //Restore stated class name stack
        $this->popCallerStatedClassName();

        return $this;
    }

    /**
     * Internal method to find, in enabled stated, the method/closure required by caller to call it. It can be directly
     * called by children class. (Protected method).
     *
     * @api
     *
     * @param string $methodName
     * @param array  $arguments  of the callmethod
     *
     * @return mixed
     *
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws \Exception
     */
    protected function findAndCall(string $methodName, array &$arguments)
    {
        //Get the visibility scope forbidden to call to a protected or private method from not allowed method
        $scopeVisibility = $this->getVisibilityScope(4);

        $activeStateFound = false;
        $returnValue = null;

        $callback = function (&$value) use (&$returnValue, &$activeStateFound, $methodName) {
            if (true === $activeStateFound) {
                throw new Exception\AvailableSeveralMethodImplementations(
                    "Method \"$methodName\" has several implementations in different states"
                );
            }

            $returnValue = $value;
            $activeStateFound = true;
        };

        //browse all enabled state to find the method
        foreach ($this->activesStates as $activeStateObject) {
            $this->callMethod($activeStateObject, $methodName, $arguments, $scopeVisibility, $callback);
        }

        if (true === $activeStateFound) {
            return $returnValue;
        }

        throw new Exception\MethodNotImplemented(
            \sprintf('Method "%s" is not available with actives states', $methodName)
        );
    }

    /**
     * To test if the identifier is an non empty string and a valif full qualified class/interface name.
     *
     * @param string $name
     *
     * @return bool
     *
     * @throws Exception\IllegalName   when the identifier is not a valid full qualified class/interface  name
     * @throws Exception\StateNotFound when the state class name does not exist
     */
    protected function validateName(string &$name): bool
    {
        if (empty($name)) {
            throw new Exception\IllegalName('Error, the identifier is not a valid string');
        }

        if (!\class_exists($name) && !\interface_exists($name)) {
            throw new Exception\StateNotFound("Error, the state $name is not available");
        }

        if (isset($this->statesAliasesList[$name])) {
            $name = $this->statesAliasesList[$name];
        }

        return true;
    }

    /**
     * Method to call into the constructor to initialize proxy's vars.
     * Externalized from the constructor to allow developers to write their own constructors into theirs classes.
     */
    protected function initializeProxy()
    {
        //Initialize internal vars
        $this->states = [];
        $this->activesStates = [];
        $this->callerStatedClassesStack = new \SplStack();
        //Creates
        $this->loadStates();
    }

    /**
     * To compute the visibility scope from the object instance of the caller.
     *
     * Called from another class (not a child class), via a static method or an instance of this class : Public scope
     * Called from a child class, via a static method or an instance of this class : Protected scope
     * Called from a static method of this stated class, or from a method of this stated class (but not this instance) :
     *  Private scope
     * Called from a method of this stated class instance : Private state
     *
     * @param object $callerObject
     *
     * @return string
     */
    private function extractVisibilityScopeFromObject($callerObject): string
    {
        if ($this === $callerObject) {
            //It's me ! Mario ! So Private scope
            return StateInterface::VISIBILITY_PRIVATE;
        }

        if (\get_class($this) === \get_class($callerObject)) {
            //It's a brother (another instance of this same stated class, not a child), So Private scope too
            return StateInterface::VISIBILITY_PRIVATE;
        }

        if ($callerObject instanceof $this) {
            //It's a child class, so Protected.
            return StateInterface::VISIBILITY_PROTECTED;
        }

        //All another case (not same class), public scope
        return StateInterface::VISIBILITY_PUBLIC;
    }

    /**
     * To compute the visibility scope from the class name of the caller :.
     *
     * Called from a child class, via a static method or an instance of this class : Protected scope
     * Called from a static method of this stated class, or from a method of this stated class (but not this instance)
     *  Private scope
     *
     * @param string $callerName
     *
     * @return string
     */
    private function extractVisibilityScopeFromClass(string $callerName): string
    {
        $thisClassName = \get_class($this);

        if (\is_subclass_of($callerName, $thisClassName, true)) {
            //It's a child class, so protected scope
            return StateInterface::VISIBILITY_PROTECTED;
        }

        if (\is_a($callerName, $thisClassName, true)) {
            //It's this class, so private scope
            return StateInterface::VISIBILITY_PRIVATE;
        }

        //All another case (not same class), public scope
        return StateInterface::VISIBILITY_PUBLIC;
    }

    /**
     * To determine the caller visibility scope to not grant to call protected or private method from an external
     * object.
     * getVisibilityScope() uses debug_backtrace() to get last entries in the calling stack.
     *  (PHP does not provide a method to get this, but the cost of to call the debug_backtrace is very light).
     * This method is used to restore the default PHP's behavior, skipped with __call() method : PHP is naturally not
     * able to detect it : because __call, like all class's methods can access to all private and protected methods.
     *
     * Called from the main block : Public scope
     * Called from a global function : Public scope
     * Called from another class (not a child class), via a static method or an instance of this class : Public scope
     * Called from a child class, via a static method or an instance of this class : Protected scope
     * Called from a static method of this stated class, or from a method of this stated class (but not this instance) :
     *  Private scope
     * Called from a method of this stated class instance : Private state
     *
     * @param int $limit To define the caller into the calling stack
     *
     * @return string Return :  StateInterface::VISIBILITY_PUBLIC
     *                StateInterface::VISIBILITY_PROTECTED
     *                StateInterface::VISIBILITY_PRIVATE
     */
    private function getVisibilityScope(int $limit): string
    {
        //Get the calling stack
        $callingStack = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, (int) $limit);

        if (isset($callingStack[2]['function']) && '__call' !== $callingStack[2]['function']) {
            //Magic method __call adds a line into calling stack, but not other magic method
            --$limit;
        }

        if (\count($callingStack) >= $limit) {
            //If size of the calling stack is less : called from main php file, or corrupted stack :
            //apply default behavior : Public
            $callerLine = \array_pop($callingStack);

            if (!empty($callerLine['object']) && \is_object($callerLine['object'])) {
                //It is an object
                $callerObject = $callerLine['object'];

                return $this->extractVisibilityScopeFromObject($callerObject);
            }

            if (!empty($callerLine['class'])
                && \is_string($callerLine['class'])
                && \class_exists($callerLine['class'], false)) {
                //It is a class
                $callerName = $callerLine['class'];

                return $this->extractVisibilityScopeFromClass($callerName);
            }
        }

        //All another case (not same class), public
        //Info, If Calling stack is corrupted or in unknown state (the stack's size is less than the excepted size),
        //use default method : public
        return StateInterface::VISIBILITY_PUBLIC;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $this->cloneProxy();

        return $this;
    }

    /**
     * Helper to clone proxy's values, callable easily if the Proxy class implements it's own
     * __clone() method without do a conflict traits resolution / renaming.
     */
    public function cloneProxy(): ProxyInterface
    {
        //Clone states stack
        if (!empty($this->states)) {
            $clonedStatesArray = [];
            foreach ($this->states as $key => $state) {
                //Clone each stated class instance
                $clonedState = clone $state;
                //Update new stack
                $clonedStatesArray[$key] = $clonedState;
            }
            $this->states = $clonedStatesArray;
        }

        //Enabling states
        if (!empty($this->activesStates)) {
            $activesStates = \array_keys($this->activesStates);
            $this->activesStates = [];
            foreach ($activesStates as $stateName) {
                $this->enableState($stateName);
            }
        }

        return $this;
    }

    /***********************
     *** States Management *
     ***********************/

    /**
     * {@inheritdoc}
     */
    public function registerState(
        string $stateName,
        StateInterface $stateObject,
        string $originalClassName = ''
    ): ProxyInterface {
        $this->validateName($stateName);

        if (!\is_a($stateObject, $stateName) && !\is_subclass_of($stateObject, $stateName)) {
            throw new Exception\IllegalName(
                sprintf(
                    'Error, the state does not implement the class or interface "%s"',
                    $stateName
                )
            );
        }

        $this->states[$stateName] = $stateObject;

        if (empty($originalClassName)) {
            $originalClassName = \get_class($this);
        }

        $this->classesByStates[$stateName] = $originalClassName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function unregisterState(string $stateName): ProxyInterface
    {
        $this->validateName($stateName);

        if (!isset($this->states[$stateName])) {
            throw new Exception\StateNotFound(\sprintf('State "%s" is not available', $stateName));
        }

        unset($this->states[$stateName]);

        if (isset($this->activesStates[$stateName])) {
            unset($this->activesStates[$stateName]);
        }

        if (isset($this->classesByStates[$stateName])) {
            unset($this->classesByStates[$stateName]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function switchState(string $stateName): ProxyInterface
    {
        $this->validateName($stateName);

        $this->disableAllStates();
        $this->enableState($stateName);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function enableState(string $stateName): ProxyInterface
    {
        $this->validateName($stateName);

        if (isset($this->states[$stateName])) {
            $this->activesStates[$stateName] = $this->states[$stateName];
        } else {
            throw new Exception\StateNotFound(\sprintf('State "%s" is not available', $stateName));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function disableState(string $stateName): ProxyInterface
    {
        $this->validateName($stateName);

        if (isset($this->activesStates[$stateName])) {
            unset($this->activesStates[$stateName]);
        } else {
            throw new Exception\StateNotFound(\sprintf('State "%s" is not available', $stateName));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function disableAllStates(): ProxyInterface
    {
        $this->activesStates = [];

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    protected function listEnabledStates(): array
    {
        if (!empty($this->activesStates) && \is_array($this->activesStates)) {
            return \array_keys($this->activesStates);
        } else {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isInState(array $statesNames, callable $callback): ProxyInterface
    {
        $enabledStatesList = $this->listEnabledStates();

        sort($enabledStatesList);
        $statesNamesList = \array_flip($enabledStatesList);

        foreach ($statesNames as $stateName) {
            $this->validateName($stateName);

            if (isset($statesNamesList[$stateName])) {
                $callback($enabledStatesList);
                break;
            }

            foreach ($enabledStatesList as $enableStateName) {
                if (\is_subclass_of($enableStateName, $stateName)) {
                    $callback($enabledStatesList);
                    return $this;
                }
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __call(string $name, array $arguments)
    {
        return $this->findAndCall($name, $arguments);
    }
}
