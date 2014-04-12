<?php
/**
 * States
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@uni-alteri.com so we can send you a copy immediately.
 *
 * @package     States
 * @subpackage  Tests
 * @copyright   Copyright (c) 2009-2014 Uni Alteri (http://agence.net.ua)
 * @link        http://teknoo.it/states Project website
 * @license     http://teknoo.it/states/license/new-bsd     New BSD License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 * @version     0.9.2
 */

namespace UniAlteri\Tests\Support;

use \UniAlteri\States\Proxy\Exception;
use \UniAlteri\States\Proxy;
use \UniAlteri\States\DI;
use \UniAlteri\States;

/**
 * Class MockProxy
 * Mock proxy to tests factories behavior and trait state behavior
 *
 * @package     States
 * @subpackage  Tests
 * @copyright   Copyright (c) 2009-2014 Uni Alteri (http://agence.net.ua)
 * @link        http://teknoo.it/states Project website
 * @license     http://teknoo.it/states/license/new-bsd     New BSD License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 */
class MockProxy implements Proxy\ProxyInterface
{
    /**
     * To test args passed by factory
     * @var null|array
     */
    public $args = null;

    /**
     * Local registry of loaded states, to simulate a real proxy
     * @var array
     */
    protected $_states = array();

    /**
     * Local registry of active states, to simulate a real proxy
     * @var array
     */
    protected $_actives = array();

    /**
     * @param mixed $arguments
     */
    public function __construct($arguments)
    {
        $this->args = $arguments;
    }

    /**
     * Register a DI container for this object
     * @param DI\ContainerInterface $container
     * @return $this
     */
    public function setDIContainer(DI\ContainerInterface $container)
    {
        return $this;
    }

    /**
     * Return the DI Container used for this object
     * @return DI\ContainerInterface
     */
    public function getDIContainer()
    {
        //Not used in tests
    }

    /**
     * Return a stable unique id of the current object. It must identify
     * @return string
     */
    public function getObjectUniqueId()
    {
        //Not used in tests
    }

    /**
     * Called to clone an Object
     * @return $this
     */
    public function __clone()
    {
        //Not used in tests
    }

    /***********************
     *** States Management *
     ***********************/

    /**
     * Register dynamically a new state for this object
     * @param string $stateName
     * @param States\States\StateInterface $stateObject
     * @return $this
     */
    public function registerState($stateName, States\States\StateInterface $stateObject)
    {
        //Simulate real behavior
        $this->_states[$stateName] = $stateObject;
    }

    /**
     * Remove dynamically a state from this object
     * @param string $stateName
     * @return $this
     */
    public function unregisterState($stateName)
    {
        //Simulate real behavior
        if (isset($this->_states[$stateName])) {
            unset($this->_states[$stateName]);
        }
    }

    /**
     * Disable all actives states and load the required states
     * @param string $stateName
     * @return $this
     */
    public function switchState($stateName)
    {
        //Simulate real behavior
        $this->_actives = array($stateName => $stateName);
    }

    /**
     * Enable a loaded states
     * @param $stateName
     * @return $this
     * @throws Exception\StateNotFound if $stateName does not exist
     */
    public function enableState($stateName)
    {
        //Simulate real behavior
        $this->_actives[$stateName] = $stateName;
    }

    /**
     * Disable an active state (not available for calling, but already loaded)
     * @param string $stateName
     * @return $this
     */
    public function disableState($stateName)
    {
        //Simulate real behavior
        if (isset($this->_actives[$stateName])) {
            unset($this->_actives[$stateName]);
        }
    }

    /**
     * Disable all actives states
     * @return $this
     */
    public function disableAllStates()
    {
        //Simulate real behavior
        $this->_actives = array();
    }

    /**
     * List all available states for this object. Include added dynamically states, exclude removed dynamically states
     * @return string[]
     */
    public function listAvailableStates()
    {
        //Simulate real behavior
        return array_keys($this->_states);
    }

    /**
     * List all enable states for this object.
     * @return string[]
     */
    public function listActivesStates()
    {
        //Simulate real behavior
        return array_keys($this->_actives);
    }

    /**
     * Return the current injection closure object to access to its static properties
     * @return DI\InjectionClosureInterface
     * @throws Exception\UnavailableClosure
     */
    public function getStatic()
    {
        //Not used in tests
    }

    /*******************
     * Methods Calling *
     *******************/

    /**
     * Call a method of the Object.
     * Hooks and event are automatically called
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function __call($name, $arguments)
    {
        //Not used in tests
    }

    /**
     * Return the description of the method
     * @param string $methodName
     * @param string $stateName : Return the description for a specific state of the object, if null, use the current state
     * @return \ReflectionMethod
     * @throws Exception\StateNotFound is the state required is not available
     */
    public function getMethodDescription($methodName, $stateName = null)
    {
        //Not used in tests
    }

    /**
     * To invoke an object as a function
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function __invoke()
    {
        //Not used in tests
    }


    /*******************
     * Data Management *
     *******************/

    /**
     * Get a property of the object
     * Hooks and event are automatically called
     * @param string $name
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function __get($name)
    {
        //Not used in tests
    }

    /**
     * Test if a property is set for the object
     * Hooks and event are automatically called
     * @param string $name
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function __isset($name)
    {
        //Not used in tests
    }

    /**
     * Update a property of the object
     * Hooks and event are automatically called
     * @param string $name
     * @param string $value
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function __set($name, $value)
    {
        //Not used in tests
    }

    /**
     * To remove a property of the object
     * Hooks and event are automatically called
     * @param string $name
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function __unset($name)
    {
        //Not used in tests
    }

    /**
     * To transform the object to a string
     * Hooks and event are automatically called
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function __toString()
    {
        //Not used in tests
    }

    /****************
     * Array Access *
     ****************/

    /**
     * This method is executed when using the count() function on an object implementing Countable.
     * @return int
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function count()
    {
        //Not used in tests
    }

    /**
     * Whether or not an offset exists.
     * This method is executed when using isset() or empty() on states implementing ArrayAccess.
     * @param string|int $offset
     * @return bool
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function offsetExists($offset)
    {
        //Not used in tests
    }

    /**
     * Returns the value at specified offset.
     * This method is executed when checking if offset is empty().
     * @param string|int $offset
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function offsetGet($offset)
    {
        //Not used in tests
    }

    /**
     * Assigns a value to the specified offset.
     * @param string|int $offset
     * @param mixed $value
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function offsetSet($offset, $value)
    {
        //Not used in tests
    }

    /**
     * Unset an offset.
     * @param string|int $offset
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function offsetUnset($offset)
    {
        //Not used in tests
    }

    /************
     * Iterator *
     ************/

    /**
     * Returns the current element.
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function current()
    {
        //Not used in tests
    }

    /**
     * Returns the key of the current element.
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function key()
    {
        //Not used in tests
    }

    /**
     * Moves the current position to the next element.
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function next()
    {
        //Not used in tests
    }

    /**
     * Rewinds back to the first element of the Iterator.
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function rewind()
    {
        //Not used in tests
    }

    /**
     * Seeks to a given position in the iterator.
     * @param int $position
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function seek($position)
    {
        //Not used in tests
    }

    /**
     * This method is called after Iterator::rewind() and Iterator::next() to check if the current position is valid.
     * @return bool
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function valid()
    {
        //Not used in tests
    }

    /**
     * Returns an external iterator.
     * @return \Traversable
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function getIterator()
    {
        //Not used in tests
    }

    /*****************
     * Serialization *
     *****************/

    /**
     * To serialize the object
     * Hooks and event are automatically called
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     * @return string
     */
    public function serialize()
    {
        //Not used in tests
    }

    /**
     * To wake up the object
     * Hooks and event are automatically called
     * @param string $serialized
     * @throws Exception\MethodNotImplemented if any enable state implement the required method
     * @throws Exception\UnavailableState if the required state is not available
     */
    public function unserialize($serialized)
    {
        //Not used in tests
    }
}