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
 * @project     States
 * @category    Factory
 * @copyright   Copyright (c) 2009-2014 Uni Alteri (http://agence.net.ua)
 * @license     http://agence.net.ua/states/license/new-bsd     New BSD License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 * @version     $Id$
 */

namespace UniAlteri\States\Factory;

use \UniAlteri\States;
use \UniAlteri\States\DI;
use \UniAlteri\States\Loader;
use \UniAlteri\States\Proxy;

class FactoryAbstract implements FactoryInterface
{

    /**
     * DI Container to use with this factory object
     * @var DI\ContainerInterface
     */
    protected $_diContainer = null;

    /**
     * Register a DI container for this object
     * @param DI\ContainerInterface $container
     */
    public function setDIContainer(DI\ContainerInterface $container)
    {
        $this->_diContainer = $container;
    }

    /**
     * Return the DI Container used for this object
     * @return DI\ContainerInterface
     */
    public function getDIContainer()
    {
        return $this->_diContainer;
    }

    /**
     * Return the loader of this stated class from its DI Container
     * @return Loader\FactoryInterface
     * @throws Exception\UnavailableLoader if any loader are available for this stated class
     */
    protected function _getLoader()
    {
        $factoryLoader = $this->_diContainer->get(Loader\FactoryInterface::DI_FACTORY_NAME);
        if (!$factoryLoader instanceof Loader\FactoryInterface) {
            throw new Exception\UnavailableLoader('Error, the loader is not available');
        }

        return $factoryLoader;
    }

    /**
     * Build a new instance of an object
     * @param mixed $arguments
     * @param string $stateName to build an object with a specific class
     * @return States\ObjectInterface
     * @throws Exception\StateNotFound if the $stateName was not found for this stated class
     * @throws Exception\UnavailableLoader if any loader are available for this stated class
     */
    public function build($arguments=null, $stateName=null)
    {
        //Get factory loader
        $factoryLoader = $this->_getLoader();

        //Build a new proxy object
        $proxyObject = $factoryLoader->loadProxy();
        $diContainerObject = $this->getDIContainer();

        //Get all states available
        $statesList = $factoryLoader->listStates();

        //Check if the default state is available
        $statesList = array_combine($statesList, $statesList);
        $defaultStatedName = Proxy\ProxyInterface::DEFAULT_STATE_NAME;
        if (!isset($statesList[$defaultStatedName])) {
            throw new Exception\StateNotFound('Error, the state "'.$defaultStatedName.'" was not found in this stated class');
        }

        //Check if the require state is available
        if (null !== $stateName && !isset($statesList[$stateName])) {
            throw new Exception\StateNotFound('Error, the state "'.$stateName.'" was not found in this stated class');
        }

        //Load each state into proxy
        foreach ($statesList as $loadingStateName) {
            $stateObject = $factoryLoader->loadState($loadingStateName);
            $stateObject->setDIContainer($diContainerObject);
            $proxyObject->registerState($loadingStateName, $stateObject);
        }

        //Switch to required state
        if (null !== $stateName) {
            $proxyObject->switchState($stateName);
        } else {
            $proxyObject->switchState($defaultStatedName);
        }

        return $proxyObject;
    }
}