<?php

/*
 * States.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Support\Extendable\Daughter;

use Teknoo\Tests\Support\Extendable\Daughter\States\StateDefault;
use Teknoo\Tests\Support\Extendable\Daughter\States\StateOne;
use Teknoo\Tests\Support\Extendable\Daughter\States\StateThree;
use Teknoo\Tests\Support\Extendable\Mother\Mother;

/**
 * Proxy Daughter
 * Proxy class of the stated class Daughter
 * Copy from Demo for functional tests.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Daughter extends Mother
{
    protected static function statesListDeclaration(): array
    {
        return [
            StateDefault::class,
            StateOne::class,
            StateThree::class,
        ];
    }
}
