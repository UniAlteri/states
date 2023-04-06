<?php

/*
 * States.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\States\States;

use Teknoo\Tests\Support;

/**
 * Class StateTest
 * Implementation of AbstractStatesTests to test the trait \Teknoo\States\State\StateTrait and
 * the abstract class \Teknoo\States\State\AbstractState.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\States\State\StateTrait
 * @covers \Teknoo\States\State\AbstractState
 */
class StateTest extends AbstractStatesTests
{
    /**
     * Build a basic object to provide only public methods.
     *
     */
    protected function getPublicClassObject(bool $privateMode, string $statedClassName, array $aliases = []): \Teknoo\Tests\Support\MockOnlyPublic
    {
        return new Support\MockOnlyPublic($privateMode, $statedClassName, $aliases);
    }

    /**
     * Build a basic object to provide only protected methods.
     *
     */
    protected function getProtectedClassObject(bool $privateMode, string $statedClassName, array $aliases = []): \Teknoo\Tests\Support\MockOnlyProtected
    {
        return new Support\MockOnlyProtected($privateMode, $statedClassName, $aliases);
    }

    /**
     * Build a basic object to provide only private methods.
     *
     */
    protected function getPrivateClassObject(bool $privateMode, string $statedClassName, array $aliases = []): \Teknoo\Tests\Support\MockOnlyPrivate
    {
        return new Support\MockOnlyPrivate($privateMode, $statedClassName, $aliases);
    }
}
