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
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/states/license/mit         MIT License
 * @license     http://teknoo.software/states/license/gpl-3.0     GPL v3 License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\States;

use Teknoo\States\Factory\FactoryInterface;

defined('UA_STATES_PATH') || define('UA_STATES_PATH', __DIR__);

//Shortcut for DIRECTORY_SEPARATOR
defined('DS') || define('DS', DIRECTORY_SEPARATOR);

//Use composer has default auto loader
$composerFile = __DIR__.'/../../../../vendor/autoload.php';
if (!file_exists($composerFile)) {
    $composerFile = __DIR__.'/../../../vendor/autoload.php';
}
$composerInstance = require $composerFile;

//Initial DI Container
$diContainer = new DI\Container();

//Initialize the Factory Repository
$diContainer->registerInstance(FactoryInterface::DI_FACTORY_REPOSITORY, new DI\Container());

/*
 * Service to generate a finder for Stated class factory
 * @param DI\ContainerInterface $container
 * @return Loader\FinderComposerIntegrated
 * @throws Exception\UnavailableFactory if the local factory is not available
 */
$finderService = function (DI\ContainerInterface $container) use ($composerInstance) {
    if (false === $container->testEntry(Factory\FactoryInterface::DI_FACTORY_NAME)) {
        throw new Exception\UnavailableFactory('Error, the factory is not available into container');
    }

    $factory = $container->get(Factory\FactoryInterface::DI_FACTORY_NAME);

    return new Loader\FinderComposerIntegrated($factory->getStatedClassName(), $factory->getPath(), $composerInstance);
};

//Register finder generator
$diContainer->registerService(Loader\FinderInterface::DI_FINDER_SERVICE, $finderService);

//Register injection closure generator
$injectionClosureService = function () {
    if (!defined('DISABLE_PHP_FLOC_OPERATOR') && '5.6' <= PHP_VERSION) {
        return new DI\InjectionClosurePHP56();
    } else {
        return new DI\InjectionClosure();
    }
};

$diContainer->registerService(States\StateInterface::INJECTION_CLOSURE_SERVICE_IDENTIFIER, $injectionClosureService);

//Stated class loader, initialize
$loader = new Loader\LoaderComposer($composerInstance);
$loader->setDIContainer($diContainer);

//Register loader into container
$diContainer->registerInstance(Loader\LoaderInterface::DI_LOADER_INSTANCE, $loader);

//Register autoload function in the spl autoloader stack
spl_autoload_register(
    array($loader, 'loadClass'),
    true,
    true
);

//Return the loader for the caller file
return $loader;