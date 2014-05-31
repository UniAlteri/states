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
 * @subpackage  Demo
 * @copyright   Copyright (c) 2009-2014 Uni Alteri (http://agence.net.ua)
 * @link        http://teknoo.it/states Project website
 * @license     http://teknoo.it/states/license/new-bsd     New BSD License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 * @version     0.9.2
 */

namespace demo;

error_reporting(E_ALL | E_STRICT);

defined('DS')
    || define('DS', DIRECTORY_SEPARATOR);

//Loading lib States
$loader = require_once(dirname(__DIR__).DS.'lib'.DS.'UniAlteri'.DS.'States'.DS.'bootstrap.php');

//Register demo namespace
$loader->registerNamespace('\\demo\\UniAlteri', __DIR__.DS.'UniAlteri');

print 'Uni Alteri - States library - Demo :'.PHP_EOL.PHP_EOL;
print 'Instantiate stated object from its phar'.PHP_EOL;
$stateObject = new UniAlteri\Single();

print 'Call the default method of the default state'.PHP_EOL;
$stateObject->methodD();