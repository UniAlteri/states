<?php

/**
 * States.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@uni-alteri.com so we can send you a copy immediately.
 *
 * @copyright   Copyright (c) 2009-2015 Uni Alteri (http://agence.net.ua)
 *
 * @link        http://teknoo.it/states Project website
 *
 * @license     http://teknoo.it/states/license/mit         MIT License
 * @license     http://teknoo.it/states/license/gpl-3.0     GPL v3 License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 */

namespace UniAlteri\States\DI;

/**
 * Interface ContainerInterface
 * Interface for dependency injection container to use in this library.
 *
 * @copyright   Copyright (c) 2009-2015 Uni Alteri (http://agence.net.ua)
 *
 * @link        http://teknoo.it/states Project website
 *
 * @license     http://teknoo.it/states/license/mit         MIT License
 * @license     http://teknoo.it/states/license/gpl-3.0     GPL v3 License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 */
interface ContainerInterface
{
    /**
     * To support the object cloning : All registries must be cloning, but not their values.
     */
    public function __clone();

    /**
     * Call an entry of the container to retrieve an instance.
     *
     * @param string $name : identifier of the instance
     *
     * @return mixed
     *
     * @throws Exception\InvalidArgument if the identifier is not defined
     * @throws Exception\IllegalName     when the identifier does not respect the pattern [a-zA-Z_][a-zA-Z0-9_\-]*
     */
    public function get($name);

    /**
     * Register a new shared object into container (the same object is returned at each call).
     *
     * @param string                 $name
     * @param object|callable|string $instance
     *
     * @return $this
     *
     * @throws Exception\ClassNotFound  if $instance is a non-existent class name
     * @throws Exception\IllegalService if the $instance is not an invokable object, or a function, or an existent class
     * @throws Exception\IllegalName    when the identifier does not respect the pattern [a-zA-Z_][a-zA-Z0-9_\-]*
     */
    public function registerInstance($name, $instance);

    /**
     * Register a new service into container (a new instance is returned at each call).
     *
     * @param string                 $name     : interface name, class name, alias
     * @param object|callable|string $instance
     *
     * @return $this
     *
     * @throws Exception\ClassNotFound  if $instance is a non-existent class name
     * @throws Exception\IllegalService if the $instance is not an invokable object, or a function, or an existent class
     * @throws Exception\IllegalName    when the identifier does not respect the pattern [a-zA-Z_][a-zA-Z0-9_\-]*
     */
    public function registerService($name, $instance);

    /**
     * Test if an entry is already registered.
     *
     * @param string $name
     *
     * @return bool
     *
     * @throws Exception\IllegalName when the identifier does not respect the pattern [a-zA-Z_][a-zA-Z0-9_\-]*
     */
    public function testEntry($name);

    /**
     * Remove an entry from the container.
     *
     * @param string $name
     *
     * @return $this
     *
     * @throws Exception\IllegalName when the identifier does not respect the pattern [a-zA-Z_][a-zA-Z0-9_\-]*
     */
    public function unregister($name);

    /**
     * Configure the container from an array (provided by an INI file or other).
     *
     * @param array|\ArrayObject $params
     *
     * @return mixed
     *
     * @throws Exception\InvalidArgument when $params is not an array or an ArrayAccess object
     */
    public function configure($params);

    /**
     * List all entries of this container.
     *
     * @return string[]
     */
    public function listDefinitions();
}