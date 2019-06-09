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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\States\Automated\Assertion;

use Teknoo\Immutable\ImmutableTrait;
use Teknoo\States\Automated\AutomatedInterface;

/**
 * class AbstractAssertion
 * Abstract implementation of AssertionInterface.
 *
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractAssertion implements AssertionInterface
{
    use ImmutableTrait;

    /**
     * List of stated to enable if the assertion is valid.
     *
     * @var string[]
     */
    private $statesList;

    /**
     * @var AutomatedInterface
     */
    private $proxy;

    /**
     * @param string|string[] $statesList
     */
    public function __construct($statesList)
    {
        $this->uniqueConstructorCheck();

        $this->statesList = (array) $statesList;
    }

    /**
     * Return the proxy linked to the check of this assertions.
     *
     * @return AutomatedInterface
     */
    protected function getProxy(): AutomatedInterface
    {
        return $this->proxy;
    }

    /**
     * Abstract method to implement into final class to proccess to the check of this assertions.
     *
     * @param AutomatedInterface $proxy
     */
    abstract protected function process(AutomatedInterface $proxy): void;

    /**
     * {@inheritdoc}
     */
    public function check(AutomatedInterface $proxy): AssertionInterface
    {
        $that = clone $this;
        $that->proxy = $proxy;

        $that->process($proxy);

        return $that;
    }

    /**
     * {@inheritdoc}
     * @throws \Teknoo\States\Proxy\Exception\StateNotFound
     */
    public function isValid(): AssertionInterface
    {
        if (!$this->proxy instanceof AutomatedInterface) {
            throw new \RuntimeException('Error, the proxy is not a valid AutomatedInterface instance');
        }

        foreach ($this->statesList as $state) {
            $this->proxy->enableState($state);
        }

        return $this;
    }
}
