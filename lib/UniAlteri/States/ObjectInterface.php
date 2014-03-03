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
 * @copyright   Copyright (c) 2009-2014 Uni Alteri (http://agence.net.ua)
 * @license     http://agence.net.ua/states/license/new-bsd     New BSD License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 * @version     $Id$
 */

namespace UniAlteri\States;
use \UniAlteri\States\DI;

/**
 * Interface ObjectInterface
 * @package UniAlteri\States
 *
 * All stated object must implement this method, even if they use another proxy interface and factory interface.
 */
interface ObjectInterface
{
    /**
     * Register a DI container for this object
     * @param DI\ContainerInterface $container
     */
    public function setDIContainer(DI\ContainerInterface $container);

    /**
     * Return the DI Container used for this object
     * @return DI\ContainerInterface
     */
    public function getDIContainer();

    /**
     * Return a stable unique id of the current object. It must identify
     * @return string
     */
    public function getObjectUniqueId();

    /**
     * Called to clone an Object
     * @return $this
     */
    public function __clone();
}