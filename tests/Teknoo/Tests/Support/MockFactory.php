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
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/states/license/mit         MIT License
 * @license     http://teknoo.software/states/license/gpl-3.0     GPL v3 License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\Support;

use Teknoo\States\DI;
use Teknoo\States\Factory;
use Teknoo\States\Factory\Exception;
use Teknoo\States\Loader;
use Teknoo\States\Proxy;

/**
 * Class MockFactory
 * Mock factory to tests proxies and loaders. Logs only all actions.
 *
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/states/license/mit         MIT License
 * @license     http://teknoo.software/states/license/gpl-3.0     GPL v3 License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class MockFactory implements Factory\FactoryInterface
{
    /**
     * To list initialized factory by loader.
     *
     * @var string[]
     */
    protected static $initializedFactoryNameArray = array();

    /**
     * @var Proxy\ProxyInterface
     */
    protected $startupProxy;

    /**
     * @var string
     */
    protected $statedClassName = null;

    /**
     * @var string
     */
    protected $path = null;

    /**
     * To return the DI Container used for this object.
     *
     * @return DI\ContainerInterface
     */
    public function getDIContainer()
    {
        //Not used in tests
    }

    /**
     * To register a DI container for this object.
     *
     * @param DI\ContainerInterface $container
     *
     * @return $this
     */
    public function setDIContainer(DI\ContainerInterface $container)
    {
        //Not used in tests
    }

    /**
     * Return the loader of this stated class from its DI Container.
     *
     * @return Loader\FinderInterface
     *
     * @throws Exception\UnavailableLoader if any finder are available for this stated class
     */
    public function getFinder()
    {
        //Build a new mock finder
        return new MockFinder($this->statedClassName, $this->path);
    }

    /**
     * Return the path of the stated class.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Return the stated class name used with this factory.
     *
     * @return string
     */
    public function getStatedClassName()
    {
        return $this->statedClassName;
    }

    /**
     * Method called by the Loader to initialize the stated class :
     *  Extends the proxy used by this stated class a child called like the stated class.
     *  => To allow developer to build new object with the operator new
     *  => To allow developer to use the operator "instanceof".
     *
     * @param string $statedClassName the name of the stated class
     * @param string $path            of the stated class
     *
     * @return bool
     */
    public function initialize($statedClassName, $path)
    {
        $this->statedClassName = $statedClassName;
        $this->path = $path;
        self::$initializedFactoryNameArray[] = $statedClassName.':'.$path;
    }

    /**
     * Method added for tests to get action logs
     * Return the list of initialized factories by the loader.
     *
     * @return string[]
     */
    public static function listInitializedFactories()
    {
        return array_values(self::$initializedFactoryNameArray);
    }

    /**
     * Method added for tests to get action logs
     * Return the list of initialized factories by the loader.
     *
     * @return string[]
     */
    public static function resetInitializedFactories()
    {
        self::$initializedFactoryNameArray = array();
    }

    /**
     * Build a new instance of an object.
     *
     * @param mixed  $arguments
     * @param string $stateName to build an object with a specific class
     *
     * @return Proxy\ProxyInterface
     *
     * @throws Exception\StateNotFound     if the $stateName was not found for this stated class
     * @throws Exception\UnavailableLoader if any loader are available for this stated class
     */
    public function build($arguments = null, $stateName = null)
    {
        //Not used in tests
    }

    /**
     * Initialize a proxy object with its container and states.
     *
     * @param Proxy\ProxyInterface $proxyObject
     * @param string               $stateName
     *
     * @return bool
     *
     * @throws Exception\StateNotFound     if the $stateName was not found for this stated class
     * @throws Exception\UnavailableLoader if any loader are available for this stated class
     */
    public function startup($proxyObject, $stateName = null)
    {
        $this->startupProxy = $proxyObject;
    }

    /**
     * Get the proxy called to startup it
     * Method added for tests to check startup behavior.
     *
     * @return Proxy\ProxyInterface
     */
    public function getStartupProxy()
    {
        return $this->startupProxy;
    }
}