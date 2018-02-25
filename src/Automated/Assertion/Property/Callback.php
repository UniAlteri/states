<?php

declare(strict_types=1);

/**
 * States.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\States\Automated\Assertion\Property;

use Teknoo\Immutable\ImmutableTrait;

/**
 * Constraint to use with Teknoo\States\Automated\Property to check if a property is valid by delegating this check
 * to a callback.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Callback extends AbstractConstraint implements ConstraintInterface
{
    use ImmutableTrait;

    /**
     * @var callable
     */
    private $callback;

    /**
     * IsGreaterOrEqualThan constructor.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->uniqueConstructorCheck();

        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function check(&$value): ConstraintInterface
    {
        $callback = $this->callback;
        $callback($value, $this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(&$value): ConstraintInterface
    {
        return parent::isValid($value);
    }
}
