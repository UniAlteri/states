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
 * @copyright   Copyright (c) 2009-2015 Uni Alteri (http://uni-alteri.com)
 *
 * @link        http://teknoo.it/states Project website
 *
 * @license     http://teknoo.it/license/mit         MIT License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 */

namespace UniAlteri\Tests\States\Factory;

use UniAlteri\States\Factory;
use UniAlteri\States\Loader\FinderInterface;

/**
 * Class IntegratedTest
 * Test the exception behavior of the integrated factory.
 *
 * @copyright   Copyright (c) 2009-2015 Uni Alteri (http://uni-alteri.com)
 *
 * @link        http://teknoo.it/states Project website
 *
 * @license     http://teknoo.it/license/mit         MIT License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 *
 * @covers UniAlteri\States\Factory\Integrated
 * @covers UniAlteri\States\Factory\FactoryTrait
 */
class IntegratedTest extends AbstractFactoryTest
{
    /**
     * Return the Factory Object Interface.
     *
     * @param FinderInterface $finder
     *
     * @return Factory\FactoryInterface
     */
    public function getFactoryObject(FinderInterface $finder)
    {
        $factory = new Factory\Integrated(
            $finder->getStatedClassName(),
            $finder,
            $this->repository
        );

        return $factory;
    }
}