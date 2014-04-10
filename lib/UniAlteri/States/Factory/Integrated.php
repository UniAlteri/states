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
use UniAlteri\States\DI;

/**
 * Class Integrated
 * @package UniAlteri\States\Factory
 * Default "stated object" factory to use with this library to build a new instance
 * of a stated class. This class is used when a stated class does not provide its own factory.
 *
 * The library creates an alias with the class's factory name and this default factory
 * to simulate a dedicated factory to this class
 */
class Integrated implements FactoryInterface
{
    use TraitFactory {
        TraitFactory::initialize as traitInitialize;
    }

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
    public function initialize($statedClassName, $path)
    {
        //Call trait's method to initialize this stated class
        $this->traitInitialize($statedClassName, $path);
        //Build the factory identifier (the proxy class name)
        $parts = explode('\\', $statedClassName);
        $statedClassName .= '\\'.array_pop($parts);
        //Register this factory into the startup factory
        StandardStartupFactory::registerFactory($statedClassName, $this);
    }
}