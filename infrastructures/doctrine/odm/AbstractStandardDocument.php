<?php

/*
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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\States\Doctrine\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Teknoo\States\Proxy\ProxyInterface;

/**
 * Class StandardDocument.
 * Default Stated class implementation with a doctrine document.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @MongoDB\MappedSuperclass
 * @MongoDB\HasLifecycleCallbacks
 */
abstract class AbstractStandardDocument implements ProxyInterface
{
    use StandardTrait;

    /**
     * @throws \Teknoo\States\Proxy\Exception\StateNotFound
     */
    public function __construct()
    {
        $this->postLoadDoctrine();
    }

    /**
     * @return array<string>
     */
    protected static function statesListDeclaration(): array
    {
        return [];
    }
}