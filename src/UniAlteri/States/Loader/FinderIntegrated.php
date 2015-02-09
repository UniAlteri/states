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
 * @subpackage  Loader
 * @copyright   Copyright (c) 2009-2015 Uni Alteri (http://agence.net.ua)
 * @link        http://teknoo.it/states Project website
 * @license     http://teknoo.it/states/license/mit         MIT License
 * @license     http://teknoo.it/states/license/gpl-3.0     GPL v3 License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 * @version     1.0.2
 */
namespace UniAlteri\States\Loader;

/**
 * Class FinderIntegrated
 * Implementation of the finder. It is used with this library to find from each stated class all states
 * and the proxy. It extends FinderStandard to use '\UniAlteri\States\Proxy\Integrated' instead of
 * '\UniAlteri\States\Proxy\Standard'
 *
 * @package     States
 * @subpackage  Loader
 * @copyright   Copyright (c) 2009-2015 Uni Alteri (http://agence.net.ua)
 * @link        http://teknoo.it/states Project website
 * @license     http://teknoo.it/states/license/mit         MIT License
 * @license     http://teknoo.it/states/license/gpl-3.0     GPL v3 License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 * @api
 */
class FinderIntegrated extends FinderStandard
{
    /**
     * Default proxy class to use when there are no proxy class
     * @var string
     */
    protected $defaultProxyClassName = '\UniAlteri\States\Proxy\Integrated';
}
