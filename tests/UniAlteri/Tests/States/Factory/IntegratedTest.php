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
 * @subpackage  Tests
 * @copyright   Copyright (c) 2009-2014 Uni Alteri (http://agence.net.ua)
 * @link        http://teknoo.it/states Project website
 * @license     http://teknoo.it/states/license/mit         MIT License
 * @license     http://teknoo.it/states/license/gpl-3.0     GPL v3 License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 * @version     0.9.9
 */

namespace UniAlteri\Tests\States\Factory;

use \UniAlteri\States\Factory;

/**
 * Class IntegratedTest
 * Test the exception behavior of the integrated factory
 *
 * @package     States
 * @subpackage  Tests
 * @copyright   Copyright (c) 2009-2014 Uni Alteri (http://agence.net.ua)
 * @link        http://teknoo.it/states Project website
 * @license     http://teknoo.it/states/license/mit         MIT License
 * @license     http://teknoo.it/states/license/gpl-3.0     GPL v3 License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 */
class IntegratedTest extends AbstractFactoryTest
{
    /**
     * Return the Factory Object Interface
     * @param  boolean                  $populateContainer to populate di container of this factory
     * @return Factory\FactoryInterface
     */
    public function getFactoryObject($populateContainer=true)
    {
        $factory = new Factory\Integrated();
        if (true === $populateContainer) {
            $factory->setDIContainer($this->_container);
        }

        return $factory;
    }

    /**
     * Test if the factory Integrated initialize the StartupFactory
     */
    public function testInitialization()
    {
        Factory\StandardStartupFactory::reset();
        $factory = $this->getFactoryObject(true);
        $factory->initialize('foo', 'bar');
        $this->assertEquals(
            array(
                'foo\\foo'
            ),
            Factory\StandardStartupFactory::listRegisteredFactory()
        );
    }
}
