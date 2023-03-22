<?php

/*
 * States.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Support\Extendable\GrandDaughter;

use Teknoo\Tests\Support\Extendable\Daughter\Daughter;
use Teknoo\Tests\Support\Extendable\GrandDaughter\States\StateFour;
use Teknoo\Tests\Support\Extendable\GrandDaughter\States\StateThree;

/**
 * Proxy GrandDaughter
 * Proxy class of the stated class GrandDaughter
 * Copy from Demo for functional tests.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class GrandDaughter extends Daughter
{
    private int $privateValueOfGrandGauther = 42;

    protected static function statesListDeclaration(): array
    {
        return [
            StateThree::class,
            StateFour::class,
        ];
    }

    public function callPrivateMethod(): int
    {
        return $this->thePrivateMethod();
    }
}
