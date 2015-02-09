<?php
/**
 * States
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@uni-alteri.com so we can send you a copy immediately.
 *
 * @subpackage  Proxy
 * @copyright   Copyright (c) 2009-2015 Uni Alteri (http://agence.net.ua)
 * @link        http://teknoo.it/states Project website
 * @license     http://teknoo.it/states/license/mit         MIT License
 * @license     http://teknoo.it/states/license/gpl-3.0     GPL v3 License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 * @version     1.0.1
 */
namespace UniAlteri\States\Proxy;

use UniAlteri\States\DI;
use UniAlteri\States;

/**
 * Trait ProxyTrait
 * Standard implementation of the "Proxy Object".
 * It is used in this library to create stated object.
 *
 * A stated object is a proxy, configured for its stated class, with its different stated objects.
 * It is a proxy because, by default, all calls are redirected to enabled states.
 * $this in all methods of the stated object (also of states' methods) points the proxy object.
 *
 * The library creates an alias with the proxy class name and this default proxy to simulate a dedicated proxy
 * to this class.
 *
 * @package     States
 * @subpackage  Proxy
 * @copyright   Copyright (c) 2009-2015 Uni Alteri (http://agence.net.ua)
 * @link        http://teknoo.it/states Project website
 * @license     http://teknoo.it/states/license/mit         MIT License
 * @license     http://teknoo.it/states/license/gpl-3.0     GPL v3 License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 */
trait ProxyTrait
{
    /**
     * DI Container to use for this object
     * @var DI\ContainerInterface
     */
    protected $diContainer = null;

    /**
     * Unique identifier of this object
     * @var string
     */
    protected $uniqueId = null;

    /**
     * List of currently enabled states
     * @var \ArrayObject|States\States\StateInterface[]
     */
    protected $activesStates = null;

    /**
     * List of available states for this stated object
     * @var \ArrayObject|States\States\StateInterface[]
     */
    protected $states = null;

    /**
     * Current closure called, if not closure called, return null
     * @var DI\InjectionClosureInterface
     */
    protected $currentInjectionClosure = null;

    /**
     * Execute a method available in a state passed in args with the injection closure
     * @param  States\States\StateInterface   $state
     * @param $methodName
     * @param  array                          $arguments
     * @param  string                         $scopeVisibility self::VISIBILITY_PUBLIC|self::VISIBILITY_PROTECTED|self::VISIBILITY_PRIVATE
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws \Exception
     */
    protected function callInState(States\States\StateInterface $state, $methodName, array &$arguments, $scopeVisibility)
    {
        //Method found, extract it
        $callingClosure = $state->getClosure($methodName, $this, $scopeVisibility);
        //Change current injection
        $previousClosure = $this->currentInjectionClosure;
        $this->currentInjectionClosure = $callingClosure;

        //Call it
        try {
            $returnValues = call_user_func_array($callingClosure, $arguments);
        } catch (\Exception $e) {
            //Restore previous closure
            $this->currentInjectionClosure = $previousClosure;
            throw $e;
        }

        //Restore previous closure
        $this->currentInjectionClosure = $previousClosure;

        return $returnValues;
    }

    /**
     * Internal method to find closure required by caller to call it
     * @param  string                         $methodName
     * @param  array                          $arguments  of the call
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     * @throws Exception\IllegalArgument      if the method's name is not a string
     * @throws \Exception
     */
    protected function findMethodToCall($methodName, array $arguments)
    {
        if (!is_string($methodName)) {
            throw new Exception\IllegalArgument('Error the methodName is not a string');
        }

        //Get the visibility scope forbidden to call to a protected or private method from not allowed method
        $scopeVisibility = $this->getVisibilityScope();

        $methodsWithStatesArray = explode('Of', $methodName);
        if (1 < count($methodsWithStatesArray)) {
            //A specific state is required for this call
            $statesName = lcfirst(array_pop($methodsWithStatesArray));
            if (isset($this->activesStates[$statesName])) {
                //Get the state name
                $methodName = implode('Of', $methodsWithStatesArray);

                $activeStateObject = $this->activesStates[$statesName];
                if (true === $activeStateObject->testMethod($methodName, $scopeVisibility)) {
                    return $this->callInState($activeStateObject, $methodName, $arguments, $scopeVisibility);
                }
            }
        }

        $activeStateFound = null;
        //No specific state required, browse all enabled state to find the method
        foreach ($this->activesStates as $activeStateObject) {
            if (true === $activeStateObject->testMethod($methodName, $scopeVisibility)) {
                if (null === $activeStateFound) {
                    //Check if there are only one enabled state whom implements this method
                    $activeStateFound = $activeStateObject;
                } else {
                    //Else, throw an exception
                    throw new Exception\AvailableSeveralMethodImplementations(
                        sprintf(
                            'Method "%s" has several implementations in different states',
                            $methodName
                        )
                    );
                }
            }
        }

        if ($activeStateFound instanceof States\States\StateInterface) {
            return $this->callInState($activeStateFound, $methodName, $arguments, $scopeVisibility);
        }

        throw new Exception\MethodNotImplemented(
            sprintf('Method "%s" is not available with actives states', $methodName)
        );
    }

    /**
     * To test if the identifier respects the pattern [a-zA-Z_][a-zA-Z0-9_\-]*
     * @param  string                    $name
     * @return bool
     * @throws Exception\IllegalArgument when the identifier is not a string
     * @throws Exception\IllegalName     when the identifier does not respect the pattern [a-zA-Z_][a-zA-Z0-9_\-]*
     */
    protected function validateName($name)
    {
        if (!is_string($name)) {
            throw new Exception\IllegalArgument('Error, the identifier is not a string');
        }

        if (1 == preg_match('#^[a-zA-Z_][a-zA-Z0-9_\-]*#iS', $name)) {
            return true;
        }

        throw new Exception\IllegalName('Error, the identifier is not a valid string');
    }

    /**
     * Initialize the proxy
     */
    public function __construct()
    {
        $this->initializeProxy();
    }

    /**
     * Method to call into the constructor to initialize proxy's vars.
     * Externalized from the constructor to allow developers to write their own constructors into theirs classes
     */
    protected function initializeProxy()
    {
        //Initialize internal vars
        $this->states = new \ArrayObject();
        $this->activesStates = new \ArrayObject();
    }

    /**
     * To register a DI container for this object
     * @param  DI\ContainerInterface $container
     * @return $this
     */
    public function setDIContainer(DI\ContainerInterface $container)
    {
        $this->diContainer = $container;

        return $this;
    }

    /**
     * To return the DI Container used for this object
     * @return DI\ContainerInterface
     */
    public function getDIContainer()
    {
        return $this->diContainer;
    }

    /**
     * To determine the caller visibility scope to not permit to call protected or private method from an external object.
     * Use debug_backtrace to get the calling stack.
     * (PHP does not provide a method to get this, but the cost of debug_backtrace is light).
     * @param  int    $limit To define the caller into the calling stack
     * @return string Return :  States\States\StateInterface::VISIBILITY_PUBLIC
     *                      States\States\StateInterface::VISIBILITY_PROTECTED
     *                      States\States\StateInterface::VISIBILITY_PRIVATE
     */
    protected function getVisibilityScope($limit = 5)
    {
        //Get the calling stack
        $callingStack = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT|DEBUG_BACKTRACE_IGNORE_ARGS, intval($limit));

        if (isset($callingStack[2]['function']) && '__call' !== $callingStack[2]['function']) {
            //Magic method __call adds a line into calling stack, but not other magic method
            $limit--;
        }

        if (count($callingStack) >= $limit) {
            //If size of the calling stack is less : called from main php file, or corrupted stack :
            //apply default behavior : Public
            $callerLine = array_pop($callingStack);

            if (!empty($callerLine['object']) && is_object($callerLine['object'])) {
                //It is an object
                $callerObject = $callerLine['object'];

                if ($this === $callerObject) {
                    //It's me ! Mario ! Private
                    return States\States\StateInterface::VISIBILITY_PRIVATE;
                }

                if (get_class($this) === get_class($callerObject)) {
                    //It's a brother (another instance of a single class), Private
                    return States\States\StateInterface::VISIBILITY_PRIVATE;
                }

                if ($callerObject instanceof $this) {
                    //It's a child class, Protected
                    return States\States\StateInterface::VISIBILITY_PROTECTED;
                }

                //All another case (not same class), public
                return States\States\StateInterface::VISIBILITY_PUBLIC;
            }

            if (!empty($callerLine['class']) && is_string($callerLine['class']) && class_exists($callerLine['class'], false)) {
                //It is a class
                $callerName = $callerLine['class'];
                $thisClassName = \get_class($this);

                if (is_subclass_of($callerName, $thisClassName, true)) {
                    //It's a child class, Protected
                    return States\States\StateInterface::VISIBILITY_PROTECTED;
                }

                if (is_a($callerName, $thisClassName, true)) {
                    //It's this class, private
                    return States\States\StateInterface::VISIBILITY_PRIVATE;
                }
            }
        }

        //All another case (not same class), public
        //Info, If Calling stack is corrupted or in unknown state (the stack's size is less than the excepted size),
        //use default method : public
        return States\States\StateInterface::VISIBILITY_PUBLIC;
    }

    /**
     * To return a unique stable id of the current object. It must identify
     * @return string
     */
    public function getObjectUniqueId()
    {
        if (null === $this->uniqueId) {
            //Generate the unique Id
            $this->uniqueId = uniqid(sha1(microtime(true)), true);
        }

        return $this->uniqueId;
    }

    /**
     * Called to clone an Object
     * @return $this
     */
    public function __clone()
    {
        $this->uniqueId = null;

        if ($this->diContainer instanceof DI\ContainerInterface) {
            $this->diContainer = clone $this->diContainer;
        }

        //Clone states stack
        if ($this->states instanceof \ArrayObject) {
            $clonedStatesArray = new \ArrayObject();
            foreach ($this->states as $key => $state) {
                //Clone each stated object
                $clonedState = clone $state;
                //Update new stack
                $clonedStatesArray[$key] = $clonedState;
            }
            $this->states = $clonedStatesArray;
        }

        //Enabling states
        if ($this->activesStates instanceof \ArrayObject) {
            $activesStates = array_keys($this->activesStates->getArrayCopy());
            $this->activesStates = new \ArrayObject();
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
     * To register dynamically a new state for this object
     * @param  string                       $stateName
     * @param  States\States\StateInterface $stateObject
     * @return $this
     * @throws Exception\IllegalArgument    when the identifier is not a string
     * @throws Exception\IllegalName        when the identifier does not respect the pattern [a-zA-Z_][a-zA-Z0-9_\-]*
     */
    public function registerState($stateName, States\States\StateInterface $stateObject)
    {
        $this->validateName($stateName);

        $this->states[$stateName] = $stateObject;

        return $this;
    }

    /**
     * To remove dynamically a state from this object
     * @param  string                    $stateName
     * @return $this
     * @throws Exception\IllegalArgument when the identifier is not a string
     * @throws Exception\StateNotFound   when the state was not found
     * @throws Exception\IllegalName     when the identifier does not respect the pattern [a-zA-Z_][a-zA-Z0-9_\-]*
     */
    public function unregisterState($stateName)
    {
        $this->validateName($stateName);

        if (isset($this->states[$stateName])) {
            unset($this->states[$stateName]);

            if (isset($this->activesStates[$stateName])) {
                unset($this->activesStates[$stateName]);
            }
        } else {
            throw new Exception\StateNotFound(sprintf('State "%s" is not available', $stateName));
        }

        return $this;
    }

    /**
     * To disable all actives states and enable the required states
     * @param  string                    $stateName
     * @return $this
     * @throws Exception\IllegalArgument when the identifier is not a string
     * @throws Exception\IllegalName     when the identifier does not respect the pattern [a-zA-Z_][a-zA-Z0-9_\-]*
     */
    public function switchState($stateName)
    {
        $this->validateName($stateName);

        $this->disableAllStates();
        $this->enableState($stateName);

        return $this;
    }

    /**
     * To enable a loaded states
     * @param $stateName
     * @return $this
     * @throws Exception\StateNotFound   if $stateName does not exist
     * @throws Exception\IllegalArgument when the identifier is not a string
     * @throws Exception\IllegalName     when the identifier does not respect the pattern [a-zA-Z_][a-zA-Z0-9_\-]*
     */
    public function enableState($stateName)
    {
        $this->validateName($stateName);

        if (isset($this->states[$stateName])) {
            $this->activesStates[$stateName] = $this->states[$stateName];
        } else {
            throw new Exception\StateNotFound(sprintf('State "%s" is not available', $stateName));
        }

        return $this;
    }

    /**
     * To disable an active state (not available for calling, but always loaded)
     * @param  string                    $stateName
     * @return $this
     * @throws Exception\IllegalArgument when the identifier is not a string
     * @throws Exception\StateNotFound   when the state was not found
     * @throws Exception\IllegalName     when the identifier does not respect the pattern [a-zA-Z_][a-zA-Z0-9_\-]*
     */
    public function disableState($stateName)
    {
        $this->validateName($stateName);

        if (isset($this->activesStates[$stateName])) {
            unset($this->activesStates[$stateName]);
        } else {
            throw new Exception\StateNotFound(sprintf('State "%s" is not available', $stateName));
        }

        return $this;
    }

    /**
     * To disable all actives states
     * @return $this
     */
    public function disableAllStates()
    {
        $this->activesStates = new \ArrayObject();

        return $this;
    }

    /**
     * To list all currently available states for this object.
     * @return string[]
     */
    public function listAvailableStates()
    {
        if ($this->states instanceof \ArrayObject) {
            return array_keys($this->states->getArrayCopy());
        } else {
            return array();
        }
    }

    /**
     * To list all enable states for this object.
     * @return string[]
     */
    public function listEnabledStates()
    {
        if ($this->activesStates instanceof \ArrayObject) {
            return array_keys($this->activesStates->getArrayCopy());
        } else {
            return array();
        }
    }

    /**
     * Check if the current entity is in the required state defined by $stateName
     * @param  string                    $stateName
     * @return bool
     * @throws Exception\InvalidArgument when $stateName is not a valid string
     */
    public function inState($stateName)
    {
        if (!is_string($stateName) && (is_object($stateName) && !is_callable(array($stateName, '__toString')))) {
            throw new Exception\InvalidArgument('Error, $stateName is not valid');
        }

        $stateName = (string) $stateName;
        $enabledStatesList = $this->listEnabledStates();

        if (is_array($enabledStatesList) && !empty($enabledStatesList)) {
            //array_flip + isset is more efficient than in_array
            $stateName = str_replace('_', '', strtolower($stateName));
            $enabledStatesList = array_flip(
                array_map('strtolower', $enabledStatesList)
            );

            return isset($enabledStatesList[$stateName]);
        } else {
            return false;
        }
    }

    /**
     * To return the current injection closure object to access to its static properties
     * @return DI\InjectionClosureInterface
     * @throws Exception\UnavailableClosure
     */
    public function getStatic()
    {
        if (!$this->currentInjectionClosure instanceof DI\InjectionClosureInterface) {
            throw new Exception\UnavailableClosure('Error, there a no active closure currently into the proxy');
        }

        return $this->currentInjectionClosure;
    }

    /*******************
     * Methods Calling *
     *******************/

    /**
     * To call a method of the Object.
     * @param  string                         $name
     * @param  array                          $arguments
     * @return mixed
     * @throws \Exception
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     * @throws Exception\IllegalArgument      if the method's name is not a string
     */
    public function __call($name, $arguments)
    {
        return $this->findMethodToCall($name, $arguments);
    }

    /**
     * To return the description of the method
     * @param  string                         $methodName
     * @param  string                         $stateName  : Return the description for a specific state of the object,
     *                                                    if null, use the current state
     * @return \ReflectionMethod
     * @throws Exception\StateNotFound        is the state required is not available
     * @throws Exception\InvalidArgument      where $methodName or $stateName are not string
     * @throws Exception\MethodNotImplemented when the method is not currently available
     * @throws \Exception                     to rethrows unknown exceptions
     */
    public function getMethodDescription($methodName, $stateName = null)
    {
        if (!is_string($methodName)) {
            throw new Exception\InvalidArgument('Error, the method name is not a valid string');
        }

        if (null !== $stateName && !is_string($stateName)) {
            throw new Exception\InvalidArgument('Error, the state name is not a valid string');
        }

        //Retrieve the visibility scope
        $scopeVisibility = $this->getVisibilityScope(3);
        try {
            if (null === $stateName) {
                //Browse all state to find the method
                foreach ($this->states as $stateObject) {
                    if ($stateObject->testMethod($methodName, $scopeVisibility)) {
                        return $stateObject->getMethodDescription($methodName);
                    }
                }
            }

            if (null !== $stateName && isset($this->states[$stateName])) {
                //Retrieve description from the required state
                if ($this->states[$stateName]->testMethod($methodName, $scopeVisibility)) {
                    return $this->states[$stateName]->getMethodDescription($methodName);
                }
            } elseif (null !== $stateName) {
                throw new Exception\StateNotFound(sprintf('State "%s" is not available', $stateName));
            }
        } catch (States\Exception\MethodNotImplemented $e) {
            throw new Exception\MethodNotImplemented(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        } catch (\Exception $e) {
            throw $e;
        }

        //Method not found
        throw new Exception\MethodNotImplemented(
            sprintf('Method "%s" is not available for this state', $methodName)
        );
    }

    /**
     * To invoke an object as a function
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function __invoke()
    {
        return $this->findMethodToCall(__FUNCTION__, func_get_args());
    }

    /*******************
     * Data Management *
     *******************/

    /**
     * To get a property of the object.
     * @param  string                         $name
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function __get($name)
    {
        return $this->findMethodToCall(__FUNCTION__, func_get_args());
    }

    /**
     * To test if a property is set for the object.
     * @param  string                         $name
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function __isset($name)
    {
        return $this->findMethodToCall(__FUNCTION__, func_get_args());
    }

    /**
     * To update a property of the object.
     * @param  string                         $name
     * @param  string                         $value
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function __set($name, $value)
    {
        return $this->findMethodToCall(__FUNCTION__, func_get_args());
    }

    /**
     * To remove a property of the object.
     * @param  string                         $name
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function __unset($name)
    {
        return $this->findMethodToCall(__FUNCTION__, func_get_args());
    }

    /**
     * To transform the object to a string
     * You cannot throw an exception from within a __toString() method. Doing so will result in a fatal error.
     * @return mixed
     */
    public function __toString()
    {
        try {
            return $this->findMethodToCall(__FUNCTION__, func_get_args());
        } catch (\Exception $e) {
            return '';
        }
    }

    /****************
     * Array Access *
     ****************/

    /**
     * This method is executed when using the count() function on an object implementing Countable.
     * @return int
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function count()
    {
        return $this->findMethodToCall(__FUNCTION__, func_get_args());
    }

    /**
     * Whether or not an offset exists.
     * This method is executed when using isset() or empty() on states implementing ArrayAccess.
     * @param  string|int                     $offset
     * @return bool
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function offsetExists($offset)
    {
        return $this->findMethodToCall(__FUNCTION__, func_get_args());
    }

    /**
     * Returns the value at specified offset.
     * This method is executed when checking if offset is empty().
     * @param  string|int                     $offset
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function offsetGet($offset)
    {
        return $this->findMethodToCall(__FUNCTION__, func_get_args());
    }

    /**
     * Assigns a value to the specified offset.
     * @param  string|int                     $offset
     * @param  mixed                          $value
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function offsetSet($offset, $value)
    {
        return $this->findMethodToCall(__FUNCTION__, func_get_args());
    }

    /**
     * Unset an offset.
     * @param  string|int                     $offset
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function offsetUnset($offset)
    {
        $this->findMethodToCall(__FUNCTION__, array($offset));
    }

    /************
     * Iterator *
     ************/

    /**
     * Returns the current element.
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function current()
    {
        return $this->findMethodToCall(__FUNCTION__, func_get_args());
    }

    /**
     * Returns the key of the current element.
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function key()
    {
        return $this->findMethodToCall(__FUNCTION__, func_get_args());
    }

    /**
     * Moves the current position to the next element.
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function next()
    {
        return $this->findMethodToCall(__FUNCTION__, func_get_args());
    }

    /**
     * Rewinds back to the first element of the Iterator.
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function rewind()
    {
        return $this->findMethodToCall(__FUNCTION__, func_get_args());
    }

    /**
     * Seeks to a given position in the iterator.
     * @param  int                            $position
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function seek($position)
    {
        $this->findMethodToCall(__FUNCTION__, array($position));
    }

    /**
     * This method is called after Iterator::rewind() and Iterator::next() to check if the current position is valid.
     * @return bool
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function valid()
    {
        return $this->findMethodToCall(__FUNCTION__, func_get_args());
    }

    /**
     * Returns an external iterator.
     * @return \Traversable
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function getIterator()
    {
        return $this->findMethodToCall(__FUNCTION__, func_get_args());
    }

    /*****************
     * Serialization *
     *****************/

    /**
     * To serialize the object
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     * @return string
     */
    public function serialize()
    {
        return $this->findMethodToCall(__FUNCTION__, func_get_args());
    }

    /**
     * To wake up the object
     * @param  string                         $serialized
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function unserialize($serialized)
    {
        $this->findMethodToCall(__FUNCTION__, array($serialized));
    }
}