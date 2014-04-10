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
 * @subpackage  Factory
 * @copyright   Copyright (c) 2009-2014 Uni Alteri (http://agence.net.ua)
 * @link        http://teknoo.it/states Project website
 * @license     http://teknoo.it/states/license/new-bsd     New BSD License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 * @version     $Id$
 */

namespace UniAlteri\States\Factory;

use \UniAlteri\States\Proxy;
use \UniAlteri\States\DI;
use \UniAlteri\States\Loader;

/**
 * Interface FactoryInterface
 * @package UniAlteri\States\Factory
 * Interface to define "stated object" factory to use with this library to build a new instance
 * of a stated class.
 */
interface FactoryInterface
{
    /**
     * Name of this factory into the DI Container available for each stated class
     */
    const DI_FACTORY_NAME = 'Factory';

    /**
     * Register a DI container for this object
     * @param DI\ContainerInterface $container
     * @return $this
     */
    public function setDIContainer(DI\ContainerInterface $container);

    /**
     * Return the DI Container used for this object
     * @return DI\ContainerInterface
     */
    public function getDIContainer();

    /**
     * Return the loader of this stated class from its DI Container
     * @return Loader\FinderInterface
     * @throws Exception\UnavailableLoader if any finder are available for this stated class
     */
    public function getFinder();

    /**
     * Return the stated class name used with this factory
     * @return string
     */
    public function getStatedClassName();

    /**
     * Return the path of the stated class
     * @return string
     */
    public function getPath();

    /**
     * Method called by the Loader to initialize the stated class :
     *  Extends the proxy used by this stated class a child called like the stated class.
     *  => To allow developer to build new object with the operator new
     *  => To allow developer to use the operator "instanceof"
     * @param string $statedClassName the name of the stated class
     * @param string $path of the stated class
     * @return boolean
     * @throws Exception\UnavailableLoader if any finder are available for this stated class
     * @throws Exception\UnavailableDIContainer if there are no di container
     */
    public function initialize($statedClassName, $path);

    /**
     * Initialize a proxy object with its container and states
     * @param Proxy\ProxyInterface $proxyObject
     * @param string $stateName
     * @return boolean
     * @throws Exception\StateNotFound if the $stateName was not found for this stated class
     * @throws Exception\UnavailableLoader if any finder are available for this stated class
     * @throws Exception\IllegalProxy if the proxy object does not implement the interface
     * @throws Exception\UnavailableDIContainer if there are no di container
     */
    public function startup($proxyObject, $stateName=null);

    /**
     * Build a new instance of an object
     * @param mixed $arguments
     * @param string $stateName to build an object with a specific class
     * @return Proxy\ProxyInterface
     * @throws Exception\StateNotFound if the $stateName was not found for this stated class
     * @throws Exception\UnavailableLoader if any finder are available for this stated class
     * @throws Exception\UnavailableDIContainer if there are no di container
     */
    public function build($arguments=null, $stateName=null);
}