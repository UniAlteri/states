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
 * @package     States
 * @subpackage  Proxy
 * @copyright   Copyright (c) 2009-2014 Uni Alteri (http://agence.net.ua)
 * @link        http://teknoo.it/states Project website
 * @license     http://teknoo.it/states/license/new-bsd     New BSD License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 * @version     0.9.2
 */

namespace UniAlteri\States\Proxy;

use \UniAlteri\States;
use \UniAlteri\States\DI;

/**
 * Interface ProxyInterface
 * Interface to define "Proxy Object" used in this library to create stated object.
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
 * @copyright   Copyright (c) 2009-2014 Uni Alteri (http://agence.net.ua)
 * @link        http://teknoo.it/states Project website
 * @license     http://teknoo.it/states/license/new-bsd     New BSD License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 */
interface ProxyInterface extends
    States\ObjectInterface,
    \Serializable,
    \ArrayAccess,
    \SeekableIterator,
    \Countable
{
    /**
     * Name of the default state to load automatically in the construction
     */
    const DEFAULT_STATE_NAME = 'StateDefault';

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
    public function registerState($stateName, States\States\StateInterface $stateObject);

    /**
     * To remove dynamically a state from this object
     * @param  string                    $stateName
     * @return $this
     * @throws Exception\IllegalArgument when the identifier is not a string
     * @throws Exception\StateNotFound   when the state was not found
     * @throws Exception\IllegalName     when the identifier does not respect the pattern [a-zA-Z_][a-zA-Z0-9_\-]*
     */
    public function unregisterState($stateName);

    /**
     * To disable all actives states and enable the required states
     * @param  string                    $stateName
     * @return $this
     * @throws Exception\IllegalArgument when the identifier is not a string
     * @throws Exception\IllegalName     when the identifier does not respect the pattern [a-zA-Z_][a-zA-Z0-9_\-]*
     */
    public function switchState($stateName);

    /**
     * To enable a loaded states
     * @param $stateName
     * @return $this
     * @throws Exception\StateNotFound   if $stateName does not exist
     * @throws Exception\IllegalArgument when the identifier is not a string
     * @throws Exception\IllegalName     when the identifier does not respect the pattern [a-zA-Z_][a-zA-Z0-9_\-]*
     */
    public function enableState($stateName);

    /**
     * To disable an active state (not available for calling, but always loaded)
     * @param  string                    $stateName
     * @return $this
     * @throws Exception\IllegalArgument when the identifier is not a string
     * @throws Exception\StateNotFound   when the state was not found
     * @throws Exception\IllegalName     when the identifier does not respect the pattern [a-zA-Z_][a-zA-Z0-9_\-]*
     */
    public function disableState($stateName);

    /**
     * To disable all actives states
     * @return $this
     */
    public function disableAllStates();

    /**
     * To list all currently available states for this object.
     * @return string[]
     */
    public function listAvailableStates();

    /**
     * To list all enable states for this object.
     * @return string[]
     */
    public function listEnabledStates();

    /**
     * To return the current injection closure object to access to its static properties
     * @return DI\InjectionClosureInterface
     * @throws Exception\UnavailableClosure
     */
    public function getStatic();

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
    public function __call($name, $arguments);

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
    public function getMethodDescription($methodName, $stateName = null);

    /**
     * To invoke an object as a function
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function __invoke();

    /*******************
     * Data Management *
     *******************/

    /**
     * To get a property of the object
     * @param  string                         $name
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function __get($name);

    /**
     * To test if a property is set for the object.
     * @param  string                         $name
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function __isset($name);

    /**
     * To update a property of the object.
     * @param  string                         $name
     * @param  string                         $value
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function __set($name, $value);

    /**
     * To remove a property of the object.
     * @param  string                         $name
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function __unset($name);

    /**
     * To transform the object to a string
     * You cannot throw an exception from within a __toString() method. Doing so will result in a fatal error.
     * @return mixed
     */
    public function __toString();

    /****************
     * Array Access *
     ****************/

    /**
    This method is executed when using the count() function on an object implementing Countable.
     * @return int
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function count();

    /**
     * Whether or not an offset exists.
     * This method is executed when using isset() or empty() on states implementing ArrayAccess.
     * @param  string|int                     $offset
     * @return bool
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function offsetExists($offset);

    /**
     * Returns the value at specified offset.
     * This method is executed when checking if offset is empty().
     * @param  string|int                     $offset
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function offsetGet($offset);

    /**
     * Assigns a value to the specified offset.
     * @param  string|int                     $offset
     * @param  mixed                          $value
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function offsetSet($offset, $value);

    /**
     * Unset an offset.
     * @param  string|int                     $offset
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function offsetUnset($offset);

    /************
     * Iterator *
     ************/

    /**
     * Returns the current element.
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function current();

    /**
     * Returns the key of the current element.
     * @return mixed
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function key();

    /**
     * Moves the current position to the next element.
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function next();

    /**
     * Rewinds back to the first element of the Iterator.
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function rewind();

    /**
     * Seeks to a given position in the iterator.
     * @param  int                            $position
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function seek($position);

    /**
     * This method is called after Iterator::rewind() and Iterator::next() to check if the current position is valid.
     * @return bool
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function valid();

    /**
     * Returns an external iterator.
     * @return \Traversable
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function getIterator();

    /*****************
     * Serialization *
     *****************/

    /**
     * To serialize the object
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     * @return string
     */
    public function serialize();

    /**
     * To wake up the object
     * @param  string                         $serialized
     * @throws Exception\MethodNotImplemented if any enabled state implement the required method
     * @throws Exception\UnavailableState     if the required state is not available
     */
    public function unserialize($serialized);
}
