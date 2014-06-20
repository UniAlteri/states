<?php
/**
 * States
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that is bundled with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@uni-alteri.com so we can send you a copy immediately.
 *
 * @package     States
 * @subpackage  Tests
 * @copyright   Copyright (c) 2009-2014 Uni Alteri (http://agence.net.ua)
 * @link        http://teknoo.it/states Project website
 * @license     http://teknoo.it/states/license/new-bsd     New BSD License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 * @version     0.9.2
 */

namespace UniAlteri\Tests\States\Loader;

use UniAlteri\States\Loader;
use UniAlteri\States\Loader\Exception;
use UniAlteri\Tests\Support;

/**
 * Class LoaderStandardTest
 * Tests the excepted behavior of standard loader implementing the interface \UniAlteri\States\Loader\LoaderInterface
 *
 * @package     States
 * @subpackage  Tests
 * @copyright   Copyright (c) 2009-2014 Uni Alteri (http://agence.net.ua)
 * @link        http://teknoo.it/states Project website
 * @license     http://teknoo.it/states/license/new-bsd     New BSD License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 */
class LoaderStandardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Loader to test
     * @var Loader\LoaderInterface
     */
    protected $_loader = null;

    /**
     * @var Support\MockIncludePathManager
     */
    protected $_includePathManager = null;

    /**
     * Where files to generate the phar file for test are located
     * @var string
     */
    protected $_srcPharPath = null;

    /**
     * Where the phar file for test is located
     * @var string
     */
    protected $_pharFileNamespace = null;

    /**
     * Prepare environment before test
     */
    protected function setUp()
    {
        $this->_includePathManager = new Support\MockIncludePathManager();

        //Build phar archives
        $this->_srcPharPath = dirname(dirname(dirname(__FILE__))).'/Support/src/';
        //namespace
        $this->_pharFileNamespace = dirname(dirname(dirname(__FILE__))).'/Support/pharFileNamespace.phar';

        if (0 == ini_get('phar.readonly') && !file_exists($this->_pharFileNamespace)) {
            $phar = new \Phar($this->_pharFileNamespace, 0, 'pharFileNamespace.phar');
            $phar->buildFromDirectory($this->_srcPharPath.'/NamespaceLoader/');
        }

        parent::setUp();
    }

    /**
     * Clean environment after test
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test if this suit test can run test on Phar
     * @return boolean
     */
    protected function _pharTestsAreAvailable()
    {
        return (class_exists('\Phar', false) && file_exists($this->_pharFileNamespace));
    }

    /**
     * Load object to test it
     * @param  boolean               $standardIncludePathManager to load the standard Include Path Manager from this lib and not
     *                                                           the test manager
     * @return Loader\LoaderStandard
     */
    protected function _initializeLoader($standardIncludePathManager=false)
    {
        if (false == $standardIncludePathManager) {
            $this->_loader = new Loader\LoaderStandard($this->_includePathManager);
        } else {
            $this->_loader = new Loader\LoaderStandard(new Loader\IncludePathManager());
        }

        $this->_loader->setDIContainer(new Support\MockDIContainer());
        $this->_loader->getDIContainer()->registerService(
            Loader\FinderInterface::DI_FINDER_SERVICE,
            function () {
                return new Support\MockFinder('', '');
            }
        );

        return $this->_loader;
    }

    /**
     * the loader must throw an exception Exception\IllegalArgument if the IncludePathManager does not implement the interface IncludePathManagerInterface
     */
    public function testConstructWithBadManager()
    {
        try {
            new Loader\LoaderStandard(new \stdClass());
        } catch (Exception\IllegalArgument $e) {
            return;
        } catch (\Exception $e) {}

        $this->fail('Error, the loader must throw an exception Exception\IllegalArgument if the IncludePathManager does not implement the interface IncludePathManagerInterface');
    }

    /**
     * Test exception when the Container is not valid when we set a bad object as di container
     */
    public function testSetDiContainerBad()
    {
        $object = new Loader\LoaderStandard($this->_includePathManager);
        try {
            $object->setDIContainer(new \DateTime());
        } catch (\Exception $e) {
            return;
        }

        $this->fail('Error, the object must throw an exception when the DI Container is not valid');
    }

    /**
     * Test behavior for methods Set And GetDiContainer
     */
    public function testSetAndGetDiContainer()
    {
        $object = new Loader\LoaderStandard($this->_includePathManager);
        $this->assertNull($object->getDIContainer());
        $virtualContainer = new Support\MockDIContainer();
        $this->assertSame($object, $object->setDIContainer($virtualContainer));
        $this->assertSame($virtualContainer, $object->getDIContainer());
    }

    /**
     * Test behavior of the loader when it cannot found and load the required class : it must return false
     * and give the hand to another loader
     */
    public function testLoadClassNonExistent()
    {
        $this->assertFalse($this->_initializeLoader()->loadClass('badName'));
    }

    /**
     * Loader can accept additional include path during loading process.
     * It must throw an exception if the required directory is not available
     */
    public function testAddIncludePathBadDir()
    {
        $loader = $this->_initializeLoader();
        try {
            $loader->addIncludePath('badPath');
        } catch (Exception\UnavailablePath $e) {
            return;
        } catch (\Exception $e) { }

        $this->fail('Error, if the path to include is unavailable, the loader must throws the exception Exception\UnavailablePath');
    }

    /**
     * Loader can accept additional include path during loading process.
     */
    public function testAddIncludePath()
    {
        $loader = $this->_initializeLoader();
        $loader->addIncludePath(__DIR__);
        $loader->addIncludePath(dirname(__DIR__));
        $this->assertEquals(
            array(
                __DIR__,
                dirname(__DIR__)
            ),
            array_values($loader->getIncludedPaths()->getArrayCopy())
        );
    }

    /**
     * Developers can register several namespace with several locations into the loader to accelerate the loading process
     * If the location is invalid, loader must throws exception
     */
    public function testRegisterNamespaceBadName()
    {
        $loader = $this->_initializeLoader();
        try {
            $loader->registerNamespace('badNamespace', array());
        } catch (Exception\IllegalArgument $e) {
            return;
        } catch (\Exception $e) { }

        $this->fail('Error, if the path of namespace to register is not a valid string, the loader must throws the exception Exception\UnavailablePath');
    }

    /**
     * Developers can register several namespace with several locations into the loader to accelerate the loading process
     */
    public function testRegisterNamespace()
    {
        $loader = $this->_initializeLoader();
        $loader->registerNamespace('namespace1', 'path1');
        $loader->registerNamespace('namespace2', 'path2');
        $this->assertEquals(
            array(
                '\\namespace1'    => new \SplQueue(array('path1')),
                '\\namespace2'    => new \SplQueue(array('path2')),
            ),
            $loader->listNamespaces()->getArrayCopy()
        );
    }

    /**
     * Developers can register several namespace with several locations into the loader to accelerate the loading process
     * A same namespace can accept several locations
     */
    public function testRegisterNamespaceMultiplePath()
    {
        $loader = $this->_initializeLoader();
        $loader->registerNamespace('namespace2', 'path2');
        $loader->registerNamespace('namespace1', 'path1');
        $loader->registerNamespace('namespace1', 'path3');
        $this->assertEquals(
            array(
                '\\namespace1'    => new \SplQueue(array('path1', 'path3')),
                '\\namespace2'    => new \SplQueue(array('path2')),
            ),
            $loader->listNamespaces()->getArrayCopy()
        );
    }

    /**
     * Loader can accept additional include path during loading process.
     * It can overload include path before loading, but, after the loading process, it must restore them
     */
    public function testLoadClassRestoreOldIncludedPathAfterCalling()
    {
        $this->_includePathManager->resetAllChangePath();
        $this->_includePathManager->setIncludePath(
            array(
                __DIR__
            )
        );
        $loader = $this->_initializeLoader();
        $loader->addIncludePath(dirname(__DIR__));
        $loader->loadClass('fakeClass');
        $this->assertEquals(
            array(
                __DIR__
            ),
            $this->_includePathManager->getIncludePath()
        );

        $this->assertEquals(
            array(
                array(
                    __DIR__
                ),
                array(
                    __DIR__,
                    dirname(__DIR__)
                ),
                array(
                    __DIR__
                )
            ),
            $this->_includePathManager->getAllChangePaths()
        );
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found, the loader must throw an exception
     */
    public function testBuildFactoryNonExistentFactory()
    {
        $loader = $this->_initializeLoader();
        try {
            $loader->buildFactory('badFactory', 'statedClassName', 'path');
        } catch (Exception\UnavailableFactory $e) {
            return;
        } catch (\Exception $e) { }

        $this->fail('Error, if factory\'s class was not found, Loader must throws the exception Exception\UnavailableFactory');
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory class does not implement the method Factory\FactoryInterface, the loader must throw an exception.
     */
    public function testBuildFactoryBadFactory()
    {
        $loader = $this->_initializeLoader();
        try {
            $loader->buildFactory('stdClass', 'statedClassName', 'path');
        } catch (Exception\IllegalFactory $e) {
            return;
        } catch (\Exception $e) { }

        $this->fail('Error, if factory\'s class does not implement the factory interface, Loader must throws the exception Exception\IllegalFactory');
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     */
    public function testBuildFactory()
    {
        $loader = $this->_initializeLoader();
        $this->assertEquals(array(), Support\MockFactory::listInitializedFactories());
        $loader->buildFactory('\\UniAlteri\\Tests\\Support\\MockFactory', 'class1', 'path1');
        $this->assertEquals(array('class1:path1'), Support\MockFactory::listInitializedFactories());
        $loader->buildFactory('\\UniAlteri\\Tests\\Support\\MockFactory', 'class2', 'path2');
        $this->assertEquals(array('class1:path1', 'class2:path2'), Support\MockFactory::listInitializedFactories());
        $loader->buildFactory('\\UniAlteri\\Tests\\Support\\MockFactory', 'class1', 'path3');
        $this->assertEquals(
            array('class1:path1', 'class2:path2', 'class1:path3'),
            Support\MockFactory::listInitializedFactories(),
            'Error, the loader must not manage factory building. If an even stated class is initialized several times, the loader must call the factory each time. '
        );
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaNameSpaceRelativeWithoutFactoryFile()
    {
        $loader = $this->_initializeLoader();
        $path = dirname(dirname(__DIR__)).'/Support/NamespaceLoader/';
        $loader->registerNamespace('UniAlteri\\Tests\\Support\\Loader', $path);
        $this->assertFalse($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class1'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaNameSpaceWithProxyRelativeWithoutFactoryFile()
    {
        $loader = $this->_initializeLoader();
        $path = dirname(dirname(__DIR__)).'/Support/NamespaceLoader/';
        $loader->registerNamespace('UniAlteri\\Tests\\Support\\Loader', $path);
        $this->assertFalse($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class1\\Class1'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaNameSpaceRelativeWithEmptyFactoryFile()
    {
        $loader = $this->_initializeLoader();
        $path = dirname(dirname(__DIR__)).'/Support/NamespaceLoader/';
        $loader->registerNamespace('UniAlteri\\Tests\\Support\\Loader', $path);
        $this->assertFalse($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class1b'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaNameSpaceWithProxyRelativeWithEmptyFactoryFile()
    {
        $loader = $this->_initializeLoader();
        $path = dirname(dirname(__DIR__)).'/Support/NamespaceLoader/';
        $loader->registerNamespace('UniAlteri\\Tests\\Support\\Loader', $path);
        $this->assertFalse($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class1b\\Class1b'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaNameSpaceRelative()
    {
        $loader = $this->_initializeLoader();
        $path = dirname(dirname(__DIR__)).'/Support/NamespaceLoader/';
        $loader->registerNamespace('UniAlteri\\Tests\\Support\\Loader', $path);
        $this->assertTrue($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class2'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaNameSpaceWithProxyRelative()
    {
        $loader = $this->_initializeLoader();
        $path = dirname(dirname(__DIR__)).'/Support/NamespaceLoader/';
        $loader->registerNamespace('UniAlteri\\Tests\\Support\\Loader', $path);
        $this->assertTrue($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class2\\Class2'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaNameSpaceAbsoluteWithoutFactoryFile()
    {
        $loader = $this->_initializeLoader();
        $path = dirname(dirname(__DIR__)).'/Support/NamespaceLoader/';
        $loader->registerNamespace('\\UniAlteri\\Tests\\Support\\Loader', $path);
        $this->assertFalse($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class1'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaNameSpaceAbsoluteWithProxyWithoutFactoryFile()
    {
        $loader = $this->_initializeLoader();
        $path = dirname(dirname(__DIR__)).'/Support/NamespaceLoader/';
        $loader->registerNamespace('\\UniAlteri\\Tests\\Support\\Loader', $path);
        $this->assertFalse($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class1\\Class1'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaNameSpaceAbsolute()
    {
        $loader = $this->_initializeLoader();
        $path = dirname(dirname(__DIR__)).'/Support/NamespaceLoader/';
        $loader->registerNamespace('\\UniAlteri\\Tests\\Support\\Loader', $path);
        $this->assertTrue($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class2'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaNameSpaceAbsoluteWithProxy()
    {
        $loader = $this->_initializeLoader();
        $path = dirname(dirname(__DIR__)).'/Support/NamespaceLoader/';
        $loader->registerNamespace('\\UniAlteri\\Tests\\Support\\Loader', $path);
        $this->assertTrue($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class2\\Class2'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaNameSpaceAbsoluteWithFactoryException()
    {
        $loader = $this->_initializeLoader();
        $path = dirname(dirname(__DIR__)).'/Support/NamespaceLoader/';
        $loader->registerNamespace('\\UniAlteri\\Tests\\Support\\Loader', $path);
        $this->assertFalse($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class3'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaNameSpaceWithProxyAbsoluteWithFactoryException()
    {
        $loader = $this->_initializeLoader();
        $path = dirname(dirname(__DIR__)).'/Support/NamespaceLoader/';
        $loader->registerNamespace('\\UniAlteri\\Tests\\Support\\Loader', $path);
        $this->assertFalse($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class3\\Class3'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaFileWithoutFileAbsolute()
    {
        $loader = $this->_initializeLoader(true);
        $path = dirname(dirname(__DIR__));
        $loader->addIncludePath($path);
        $this->assertFalse($loader->loadClass('\\Support\\FileLoader\\Class1'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaFileAbsolute()
    {
        $loader = $this->_initializeLoader(true);
        $path = dirname(dirname(__DIR__));
        $loader->addIncludePath($path);
        $this->assertTrue($loader->loadClass('\\Support\\FileLoader\\Class2'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaFileWithoutFile()
    {
        $loader = $this->_initializeLoader(true);
        $path = dirname(dirname(__DIR__));
        $loader->addIncludePath($path);
        $this->assertFalse($loader->loadClass('Support\\FileLoader\\Class1'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaFile()
    {
        $loader = $this->_initializeLoader(true);
        $path = dirname(dirname(__DIR__));
        $loader->addIncludePath($path);
        $this->assertTrue($loader->loadClass('Support\\FileLoader\\Class2'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassViaFileWithFactoryException()
    {
        $loader = $this->_initializeLoader(true);
        $path = dirname(dirname(__DIR__));
        $loader->addIncludePath($path);
        $this->assertFalse($loader->loadClass('Support\\FileLoader\\Class3'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassInPharViaNameSpaceRelativeWithoutFactoryFile()
    {
        if (!$this->_pharTestsAreAvailable()) {
            $this->markTestSkipped('Phar extension is not available');
            return;
        }

        $loader = $this->_initializeLoader();
        $loader->registerNamespace('UniAlteri\\Tests\\Support\\Loader', 'phar://'.$this->_pharFileNamespace);
        $this->assertFalse($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class1Phar'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassInPharViaNameSpaceWithProxyRelativeWithoutFactoryFile()
    {
        if (!$this->_pharTestsAreAvailable()) {
            $this->markTestSkipped('Phar extension is not available');
            return;
        }

        $loader = $this->_initializeLoader();
        $loader->registerNamespace('UniAlteri\\Tests\\Support\\Loader', 'phar://'.$this->_pharFileNamespace);
        $this->assertFalse($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class1Phar\\Class1Phar'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassInPharViaNameSpaceRelativeWithEmptyFactoryFile()
    {
        if (!class_exists('\Phar', false)) {
            $this->markTestSkipped('Phar extension is not available');
            return;
        }

        $loader = $this->_initializeLoader();
        $loader->registerNamespace('UniAlteri\\Tests\\Support\\Loader', 'phar://'.$this->_pharFileNamespace);
        $this->assertFalse($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class1bOharPhar'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassInPharViaNameSpaceWithProxyRelativeWithEmptyFactoryFile()
    {
        if (!class_exists('\Phar', false)) {
            $this->markTestSkipped('Phar extension is not available');
            return;
        }

        $loader = $this->_initializeLoader();
        $loader->registerNamespace('UniAlteri\\Tests\\Support\\Loader', 'phar://'.$this->_pharFileNamespace);
        $this->assertFalse($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class1bPhar\\Class1bPhar'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassInPharViaNameSpaceRelative()
    {
        if (!class_exists('\Phar', false)) {
            $this->markTestSkipped('Phar extension is not available');
            return;
        }

        $loader = $this->_initializeLoader();
        $loader->registerNamespace('UniAlteri\\Tests\\Support\\Loader', 'phar://'.$this->_pharFileNamespace);
        $this->assertTrue($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class2Phar'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassInPharViaNameSpaceWithProxyRelative()
    {
        if (!class_exists('\Phar', false)) {
            $this->markTestSkipped('Phar extension is not available');
            return;
        }

        $loader = $this->_initializeLoader();
        $loader->registerNamespace('UniAlteri\\Tests\\Support\\Loader', 'phar://'.$this->_pharFileNamespace);
        $this->assertTrue($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class2Phar\\Class2Phar'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassInPharViaNameSpaceAbsoluteWithoutFactoryFile()
    {
        if (!class_exists('\Phar', false)) {
            $this->markTestSkipped('Phar extension is not available');
            return;
        }

        $loader = $this->_initializeLoader();
        $loader->registerNamespace('\\UniAlteri\\Tests\\Support\\Loader', 'phar://'.$this->_pharFileNamespace);
        $this->assertFalse($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class1Phar'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassInPharViaNameSpaceAbsoluteWithProxyWithoutFactoryFile()
    {
        if (!class_exists('\Phar', false)) {
            $this->markTestSkipped('Phar extension is not available');
            return;
        }

        $loader = $this->_initializeLoader();
        $loader->registerNamespace('\\UniAlteri\\Tests\\Support\\Loader', 'phar://'.$this->_pharFileNamespace);
        $this->assertFalse($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class1Phar\\Class1Phar'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassInPharViaNameSpaceAbsolute()
    {
        if (!class_exists('\Phar', false)) {
            $this->markTestSkipped('Phar extension is not available');
            return;
        }

        $loader = $this->_initializeLoader();
        $loader->registerNamespace('\\UniAlteri\\Tests\\Support\\Loader', 'phar://'.$this->_pharFileNamespace);
        $this->assertTrue($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class2Phar'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassInPharViaNameSpaceAbsoluteWithProxy()
    {
        if (!class_exists('\Phar', false)) {
            $this->markTestSkipped('Phar extension is not available');
            return;
        }

        $loader = $this->_initializeLoader();
        $loader->registerNamespace('\\UniAlteri\\Tests\\Support\\Loader', 'phar://'.$this->_pharFileNamespace);
        $this->assertTrue($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class2Phar\\Class2Phar'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassInPharViaNameSpaceAbsoluteWithFactoryException()
    {
        if (!class_exists('\Phar', false)) {
            $this->markTestSkipped('Phar extension is not available');
            return;
        }

        $loader = $this->_initializeLoader();
        $loader->registerNamespace('\\UniAlteri\\Tests\\Support\\Loader', 'phar://'.$this->_pharFileNamespace);
        $this->assertFalse($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class3Phar'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory was not found (file not present, class not in the file, or exception during factory loading)
     * the loader must ignore the stated class and return false
     */
    public function testLoadClassInPharViaNameSpaceWithProxyAbsoluteWithFactoryException()
    {
        if (!class_exists('\Phar', false)) {
            $this->markTestSkipped('Phar extension is not available');
            return;
        }

        $loader = $this->_initializeLoader();
        $loader->registerNamespace('\\UniAlteri\\Tests\\Support\\Loader', 'phar://'.$this->_pharFileNamespace);
        $this->assertFalse($loader->loadClass('\\UniAlteri\\Tests\\Support\\Loader\\Class3Phar\\Class3Phar'));
    }

    /**
     * After found the stated class, the loader must load its factory and initialize it by calling its initialize() method.
     * If the factory throws an exception during its initialization, the loader must restore include path and throw the
     * exception
     */
    public function testLoadClassBehaviorDuringExceptionMustRestoreIncludedPath()
    {
        $loader = $this->_initializeLoader(false);
        $path = dirname(dirname(dirname(dirname(__DIR__))));
        $loader->addIncludePath($path);

        $fail = false;
        try {
            $loader->loadClass('UniAlteri\\Tests\\Support\\FileLoader\\Class3b');
        } catch (\Exception $e) {
            $fail = true;
        }

        $this->assertTrue($fail, 'Error, the loader must rethrow exception during loading class');
    }
}
