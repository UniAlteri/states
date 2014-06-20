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

namespace UniAlteri\Tests\States\States;

use \UniAlteri\States\DI;
use \UniAlteri\States\Proxy;
use \UniAlteri\States\States;
use \UniAlteri\Tests\Support;

/**
 * Class AbstractStatesTest
 * Set of tests to test the excepted behaviors of all implementations of \UniAlteri\States\States\StateInterface *
 *
 * @package     States
 * @subpackage  Tests
 * @copyright   Copyright (c) 2009-2014 Uni Alteri (http://agence.net.ua)
 * @link        http://teknoo.it/states Project website
 * @license     http://teknoo.it/states/license/new-bsd     New BSD License
 * @author      Richard Déloge <r.deloge@uni-alteri.com>
 */
abstract class AbstractStatesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Build an basic object to provide only public methods
     * @param  boolean                $initializeContainer initialize virtual di container for state
     * @return Support\MockOnlyPublic
     */
    abstract protected function _getPublicClassObject($initializeContainer=true);

    /**
     * Build an basic object to provide only protected methods
     * @param  boolean                   $initializeContainer initialize virtual di container for state
     * @return Support\MockOnlyProtected
     */
    abstract protected function _getProtectedClassObject($initializeContainer=true);

    /**
     * Build an basic object to provide only private methods
     * @param  boolean                 $initializeContainer initialize virtual di container for state
     * @return Support\MockOnlyPrivate
     */
    abstract protected function _getPrivateClassObject($initializeContainer=true);

    /**
     * Build a virtual proxy for test
     * @return Proxy\ProxyInterface
     */
    abstract protected function _getMockProxy();

    /**
     * Test exception when the Container is not valid when we set a bad object as di container
     */
    public function testSetDiContainerBad()
    {
        $object = $this->_getPublicClassObject(false);
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
        $object = $this->_getPublicClassObject(false);
        $this->assertNull($object->getDIContainer());
        $virtualContainer = new Support\MockDIContainer();
        $this->assertSame($object, $object->setDIContainer($virtualContainer));
        $this->assertSame($virtualContainer, $object->getDIContainer());
    }

    /**
     * Test if the state can return all its public method, without static
     */
    public function testListMethodsPublic()
    {
        $this->assertEquals(
            array(
                'standardMethod1',
                'finalMethod2',
                'standardMethod4'
            ),
            $this->_getPublicClassObject()->listMethods()->getArrayCopy()
        );
    }

    /**
     * Test if the state can return all its protected method, without static
     */
    public function testListMethodsProtected()
    {
        $this->assertEquals(
            array(
                '_standardMethod6',
                '_finalMethod7',
                '_standardMethod8'
            ),
            $this->_getProtectedClassObject()->listMethods()->getArrayCopy()
        );
    }

    /**
     * Test if the state can return all its private method, without static
     */
    public function testListMethodsPrivate()
    {
        $this->assertEquals(
            array(
                '_finalMethod9',
                '_standardMethod10',
                '_finalMethod11'
            ),
            $this->_getPrivateClassObject()->listMethods()->getArrayCopy()
        );
    }

    /**
     * Test if exception when the name is not a valid string
     */
    public function testGetBadNameMethodDescription()
    {
        try {
            $this->_getPublicClassObject()->getMethodDescription(array());
        } catch (States\Exception\InvalidArgument $e) {
            return;
        } catch (\Exception $e) {
        }

        $this->fail('Error, the state must throws an Exception\InvalidArgument exception if we require a description with an invalid string');
    }

    /**
     * Test if exception when we get a description of a non-existent method
     */
    public function testGetBadMethodDescription()
    {
        try {
            $this->_getPublicClassObject()->getMethodDescription('badMethod');
        } catch (States\Exception\MethodNotImplemented $e) {
            return;
        } catch (\Exception $e) {
        }

        $this->fail('Error, the state must throws an Exception\MethodNotImplemented exception if we require a description of non-existent method');
    }

    /**
     * Test if exception when we get a description of an ignored method, the behavior must like non-existent method
     */
    public function testGetIgnoredMethodDescriptionUsedByTrait()
    {
        try {
            $this->_getPublicClassObject()->getMethodDescription('setDIContainer');
        } catch (States\Exception\MethodNotImplemented $e) {
            return;
        } catch (\Exception $e) {
        }

        $this->fail('Error, the state must throws an Exception\MethodNotImplemented exception if we require a description of internal method of the trait');
    }

    /**
     * Test if exception when we get a description of a static method
     */
    public function testGetStaticMethodDescription()
    {
        try {
            $this->_getPublicClassObject()->getMethodDescription('staticMethod3');
        } catch (States\Exception\MethodNotImplemented $e) {
            return;
        } catch (\Exception $e) {
        }

        $this->fail('Error, the state must throws an Exception\MethodNotImplemented exception if we require a description of a static method');
    }

    /**
     * Clean description text to simplify tests
     * @param  \ReflectionMethod $text
     * @return string
     */
    protected function _formatDescription($text)
    {
        $s = trim(str_replace(array('*', '/'), '', $text->getDocComment()));
        return preg_replace('~[[:cntrl:]]~', '', $s);
    }

    /**
     * Test get method description
     */
    public function testGetMethodDescription()
    {
        $this->assertSame('Final Method 9', $this->_formatDescription($this->_getPrivateClassObject()->getMethodDescription('_finalMethod9')));
        $this->assertSame('Standard Method 10', $this->_formatDescription($this->_getPrivateClassObject()->getMethodDescription('_standardMethod10')));

        $this->assertSame('Standard Method 6      @param $a      @param $b      @return mixed', $this->_formatDescription($this->_getProtectedClassObject()->getMethodDescription('_standardMethod6')));
        $this->assertSame('Final Method 7', $this->_formatDescription($this->_getProtectedClassObject()->getMethodDescription('_finalMethod7')));

        $this->assertSame('Standard Method 1', $this->_formatDescription($this->_getPublicClassObject()->getMethodDescription('standardMethod1')));
        $this->assertSame('Final Method 2', $this->_formatDescription($this->_getPublicClassObject()->getMethodDescription('finalMethod2')));
    }

    public function testTestMethodExceptionWithInvalidName()
    {
        try {
            $this->_getPublicClassObject()->testMethod(array());
        } catch (States\Exception\InvalidArgument $e) {
            return;
        } catch (\Exception $e) {
        }

        $this->fail('Error, the state must throws an Exception\InvalidArgument exception if we require a description with an invalid string');
    }

    public function testTestMethodExceptionWithInvalidScope()
    {
        try {
            $this->_getPublicClassObject()->testMethod('standardMethod1', 'badScope');
        } catch (States\Exception\InvalidArgument $e) {
            return;
        } catch (\Exception $e) {
        }

        $this->fail('Error, the state must throws an Exception\InvalidArgument exception if we require a description with an invalid scope name');
    }

    /**
     * Test if the method exist into the state into the defined scope (private)
     */
    public function testTestMethodPrivateScope()
    {
        $private = $this->_getPrivateClassObject();
        $this->assertTrue($private->testMethod('_finalMethod9', States\StateInterface::VISIBILITY_PRIVATE));
        $this->assertTrue($private->testMethod('_finalMethod9', States\StateInterface::VISIBILITY_PRIVATE));
        $this->assertTrue($private->testMethod('_standardMethod10', States\StateInterface::VISIBILITY_PRIVATE));
        $this->assertTrue($private->testMethod('_finalMethod11', States\StateInterface::VISIBILITY_PRIVATE));
        $this->assertFalse($private->testMethod('_staticMethod12', States\StateInterface::VISIBILITY_PRIVATE));
        $this->assertFalse($private->testMethod('_staticMethod12', States\StateInterface::VISIBILITY_PRIVATE));

        $this->assertFalse($this->_getProtectedClassObject()->testMethod('_staticMethod5', States\StateInterface::VISIBILITY_PRIVATE));
        $this->assertTrue($this->_getProtectedClassObject()->testMethod('_standardMethod6', States\StateInterface::VISIBILITY_PRIVATE));
        $this->assertTrue($this->_getProtectedClassObject()->testMethod('_finalMethod7', States\StateInterface::VISIBILITY_PRIVATE));
        $this->assertTrue($this->_getProtectedClassObject()->testMethod('_standardMethod8', States\StateInterface::VISIBILITY_PRIVATE));

        $this->assertTrue($this->_getPublicClassObject()->testMethod('standardMethod1', States\StateInterface::VISIBILITY_PRIVATE));
        $this->assertTrue($this->_getPublicClassObject()->testMethod('finalMethod2', States\StateInterface::VISIBILITY_PRIVATE));
        $this->assertFalse($this->_getPublicClassObject()->testMethod('staticMethod3', States\StateInterface::VISIBILITY_PRIVATE));
        $this->assertTrue($this->_getPublicClassObject()->testMethod('standardMethod4', States\StateInterface::VISIBILITY_PRIVATE));
    }

    /**
     * Test if the method exist into the state into the defined scope (protected)
     */
    public function testTestMethodProtectedScope()
    {
        $private = $this->_getPrivateClassObject();
        $this->assertFalse($private->testMethod('_finalMethod9', States\StateInterface::VISIBILITY_PROTECTED));
        $this->assertFalse($private->testMethod('_finalMethod9', States\StateInterface::VISIBILITY_PROTECTED));
        $this->assertFalse($private->testMethod('_standardMethod10', States\StateInterface::VISIBILITY_PROTECTED));
        $this->assertFalse($private->testMethod('_finalMethod11', States\StateInterface::VISIBILITY_PROTECTED));
        $this->assertFalse($private->testMethod('_staticMethod12', States\StateInterface::VISIBILITY_PROTECTED));
        $this->assertFalse($private->testMethod('_staticMethod12', States\StateInterface::VISIBILITY_PROTECTED));

        $this->assertFalse($this->_getProtectedClassObject()->testMethod('_staticMethod5', States\StateInterface::VISIBILITY_PROTECTED));
        $this->assertTrue($this->_getProtectedClassObject()->testMethod('_standardMethod6', States\StateInterface::VISIBILITY_PROTECTED));
        $this->assertTrue($this->_getProtectedClassObject()->testMethod('_finalMethod7', States\StateInterface::VISIBILITY_PROTECTED));
        $this->assertTrue($this->_getProtectedClassObject()->testMethod('_standardMethod8', States\StateInterface::VISIBILITY_PROTECTED));

        $this->assertTrue($this->_getPublicClassObject()->testMethod('standardMethod1', States\StateInterface::VISIBILITY_PROTECTED));
        $this->assertTrue($this->_getPublicClassObject()->testMethod('finalMethod2', States\StateInterface::VISIBILITY_PROTECTED));
        $this->assertFalse($this->_getPublicClassObject()->testMethod('staticMethod3', States\StateInterface::VISIBILITY_PROTECTED));
        $this->assertTrue($this->_getPublicClassObject()->testMethod('standardMethod4', States\StateInterface::VISIBILITY_PROTECTED));
    }

    /**
     * Test if the method exist into the state into the defined scope (public)
     */
    public function testTestMethodPublicScope()
    {
        $private = $this->_getPrivateClassObject();
        $this->assertFalse($private->testMethod('_finalMethod9', States\StateInterface::VISIBILITY_PUBLIC));
        $this->assertFalse($private->testMethod('_finalMethod9', States\StateInterface::VISIBILITY_PUBLIC));
        $this->assertFalse($private->testMethod('_standardMethod10', States\StateInterface::VISIBILITY_PUBLIC));
        $this->assertFalse($private->testMethod('_finalMethod11', States\StateInterface::VISIBILITY_PUBLIC));
        $this->assertFalse($private->testMethod('_staticMethod12', States\StateInterface::VISIBILITY_PUBLIC));
        $this->assertFalse($private->testMethod('_staticMethod12', States\StateInterface::VISIBILITY_PUBLIC));

        $this->assertFalse($this->_getProtectedClassObject()->testMethod('_staticMethod5', States\StateInterface::VISIBILITY_PUBLIC));
        $this->assertFalse($this->_getProtectedClassObject()->testMethod('_standardMethod6', States\StateInterface::VISIBILITY_PUBLIC));
        $this->assertFalse($this->_getProtectedClassObject()->testMethod('_finalMethod7', States\StateInterface::VISIBILITY_PUBLIC));
        $this->assertFalse($this->_getProtectedClassObject()->testMethod('_standardMethod8', States\StateInterface::VISIBILITY_PUBLIC));

        $this->assertTrue($this->_getPublicClassObject()->testMethod('standardMethod1', States\StateInterface::VISIBILITY_PUBLIC));
        $this->assertTrue($this->_getPublicClassObject()->testMethod('finalMethod2', States\StateInterface::VISIBILITY_PUBLIC));
        $this->assertFalse($this->_getPublicClassObject()->testMethod('staticMethod3', States\StateInterface::VISIBILITY_PUBLIC));
        $this->assertTrue($this->_getPublicClassObject()->testMethod('standardMethod4', States\StateInterface::VISIBILITY_PUBLIC));
    }

    /**
     * Test if the method exist into the state into the default scope (public)
     */
    public function testTestMethodDefaultAsPublicScope()
    {
        $private = $this->_getPrivateClassObject();
        $this->assertFalse($private->testMethod('_finalMethod9'));
        $this->assertFalse($private->testMethod('_finalMethod9'));
        $this->assertFalse($private->testMethod('_standardMethod10'));
        $this->assertFalse($private->testMethod('_finalMethod11'));
        $this->assertFalse($private->testMethod('_staticMethod12'));
        $this->assertFalse($private->testMethod('_staticMethod12'));

        $this->assertFalse($this->_getProtectedClassObject()->testMethod('_staticMethod5'));
        $this->assertFalse($this->_getProtectedClassObject()->testMethod('_standardMethod6'));
        $this->assertFalse($this->_getProtectedClassObject()->testMethod('_finalMethod7'));
        $this->assertFalse($this->_getProtectedClassObject()->testMethod('_standardMethod8'));

        $this->assertTrue($this->_getPublicClassObject()->testMethod('standardMethod1'));
        $this->assertTrue($this->_getPublicClassObject()->testMethod('finalMethod2'));
        $this->assertFalse($this->_getPublicClassObject()->testMethod('staticMethod3'));
        $this->assertTrue($this->_getPublicClassObject()->testMethod('standardMethod4'));
    }

    /**
     * Test exception through by state if the name is invalid
     */
    public function testGetClosureWithInvalidName()
    {
        try {
            $this->_getPublicClassObject()->getClosure(array(), $this->_getMockProxy());
        } catch (States\Exception\InvalidArgument $e) {
            return;
        } catch (\Exception $e) {
        }

        $this->fail('Error, the state must throws an Exception\InvalidArgument exception if the method name is not a string');
    }

    /**
     * Test exception through by state if the closure method does not exist
     */
    public function testGetBadClosure()
    {
        try {
            $this->_getPublicClassObject()->getClosure('badMethod', $this->_getMockProxy());
        } catch (States\Exception\MethodNotImplemented $e) {
            return;
        } catch (\Exception $e) {
        }

        $this->fail('Error, the state must throws an Exception\MethodNotImplemented exception if the method does not exist');
    }

    /**
     * Test exception through by state if the closure method is static
     */
    public function testGetStaticClosure()
    {
        try {
            $this->_getPublicClassObject()->getClosure('staticMethod3', $this->_getMockProxy());
        } catch (States\Exception\MethodNotImplemented $e) {
            return;
        } catch (\Exception $e) {
        }

        $this->fail('Error, the state must throws an Exception\MethodNotImplemented exception if the method is static');
    }

    /**
     *
     */
    public function testGetClosureWithInvalidProxy()
    {
        try {
            $this->_getPublicClassObject()->getClosure('standardMethod1', new \DateTime());
        } catch (States\Exception\IllegalProxy $e) {
            return;
        } catch (\Exception $e) {}

        $this->fail('Error, the state must throws an Exception\MethodNotImplemented exception if the proxy does not implement the interface Proxy\ProxyInterface');
    }

    /**
     * Test exception through by state if the scope is invalid
     */
    public function testGetClosureWithInvalidScope()
    {
        try {
            $this->_getPublicClassObject()->getClosure('standardMethod1', $this->_getMockProxy(), 'badScope');
        } catch (States\Exception\InvalidArgument $e) {
            return;
        } catch (\Exception $e) {}

        $this->fail('Error, the state must throws an Exception\InvalidArgument exception if the scope is invalid');
    }

    /**
     * Test exception through by state if the closure method is static
     */
    public function testGetClosureWithInvalidDiContainer()
    {
        try {
            $object = $this->_getPublicClassObject(false);
            $object->getClosure('standardMethod1', $this->_getMockProxy());
        } catch (States\Exception\IllegalService $e) {
            return;
        } catch (\Exception $e) {}

        $this->fail('Error, the state must throws an Exception\IllegalService if no DI Container has been defined before getClosure');
    }

    /**
     * Test exception through by state if the closure method is static
     */
    public function testGetClosureWithInvalidInjectContainer()
    {
        try {
            $object = $this->_getPublicClassObject();
            $object->getDIContainer()->registerService(States\StateInterface::INJECTION_CLOSURE_SERVICE_IDENTIFIER, null);
            $object->getClosure('standardMethod1', $this->_getMockProxy());
        } catch (States\Exception\IllegalService $e) {
            return;
        } catch (\Exception $e) {}

        $this->fail('Error, the state must throws an Exception\IllegalService if no Injection Container service has been defined before getClosure');
    }

    /**
     * Test if the closure can be get into the state into the defined scope (private)
     */
    public function testGetClosureWithPrivateScope()
    {
        $closure = $this->_getPrivateClassObject()->getClosure(
            '_standardMethod10',
            $this->_getMockProxy(),
            States\StateInterface::VISIBILITY_PRIVATE
        );

        $this->assertInstanceOf('\UniAlteri\States\DI\InjectionClosureInterface', $closure);

        $closure = $this->_getProtectedClassObject()->getClosure(
            '_standardMethod6',
            $this->_getMockProxy(),
            States\StateInterface::VISIBILITY_PRIVATE
        );

        $this->assertInstanceOf('\UniAlteri\States\DI\InjectionClosureInterface', $closure);

        $closure = $this->_getPublicClassObject()->getClosure(
            'standardMethod1',
            $this->_getMockProxy(),
            States\StateInterface::VISIBILITY_PRIVATE
        );

        $this->assertInstanceOf('\UniAlteri\States\DI\InjectionClosureInterface', $closure);
    }

    /**
     * Test if the closure can be get into the state into the defined scope (protected, so privates methods are not available)
     */
    public function testGetClosureWithProtectedScope()
    {
        $fail = false;
        try {
            $this->_getPrivateClassObject()->getClosure(
                '_standardMethod10',
                $this->_getMockProxy(),
                States\StateInterface::VISIBILITY_PROTECTED
            );
        } catch (States\Exception\MethodNotImplemented $e) {
            $fail = true;
        } catch (\Exception $e) {}

        $this->assertTrue($fail, 'Error, in Protected scope, private methods are not available');

        $closure = $this->_getProtectedClassObject()->getClosure(
            '_standardMethod6',
            $this->_getMockProxy(),
            States\StateInterface::VISIBILITY_PROTECTED
        );

        $this->assertInstanceOf('\UniAlteri\States\DI\InjectionClosureInterface', $closure);

        $closure = $this->_getPublicClassObject()->getClosure(
            'standardMethod1',
            $this->_getMockProxy(),
            States\StateInterface::VISIBILITY_PROTECTED
        );

        $this->assertInstanceOf('\UniAlteri\States\DI\InjectionClosureInterface', $closure);
    }

    /**
     * Test if the closure can be get into the state into the defined scope (public)
     */
    public function testGetClosureWithPublicScope()
    {
        $fail = false;
        try {
            $this->_getPrivateClassObject()->getClosure(
                '_standardMethod10',
                $this->_getMockProxy(),
                States\StateInterface::VISIBILITY_PUBLIC
            );
        } catch (States\Exception\MethodNotImplemented $e) {
            $fail = true;
        } catch (\Exception $e) {}

        $this->assertTrue($fail, 'Error, in Public scope, private and protected methods are not available');

        $fail = false;
        try {
            $this->_getProtectedClassObject()->getClosure(
                '_standardMethod6',
                $this->_getMockProxy(),
                States\StateInterface::VISIBILITY_PUBLIC
            );
        } catch (States\Exception\MethodNotImplemented $e) {
            $fail = true;
        } catch (\Exception $e) {}

        $this->assertTrue($fail, 'Error, in Public scope, private and protected methods are not available');

        $closure = $this->_getPublicClassObject()->getClosure(
            'standardMethod1',
            $this->_getMockProxy(),
            States\StateInterface::VISIBILITY_PUBLIC
        );

        $this->assertInstanceOf('\UniAlteri\States\DI\InjectionClosureInterface', $closure);
    }

    /**
     * Test if the closure can be get into the state into the default scope (public)
     */
    public function testGetClosureWithPublicAsDefaultScope()
    {
        $fail = false;
        try {
            $this->_getPrivateClassObject()->getClosure(
                '_standardMethod10',
                $this->_getMockProxy()
            );
        } catch (States\Exception\MethodNotImplemented $e) {
            $fail = true;
        } catch (\Exception $e) {}

        $this->assertTrue($fail, 'Error, in Public scope, private and protected methods are not available');

        $fail = false;
        try {
            $this->_getProtectedClassObject()->getClosure(
                '_standardMethod6',
                $this->_getMockProxy()
            );
        } catch (States\Exception\MethodNotImplemented $e) {
            $fail = true;
        } catch (\Exception $e) {}

        $this->assertTrue($fail, 'Error, in Public scope, private and protected methods are not available');

        $closure = $this->_getPublicClassObject()->getClosure(
            'standardMethod1',
            $this->_getMockProxy()
        );

        $this->assertInstanceOf('\UniAlteri\States\DI\InjectionClosureInterface', $closure);
    }

    /**
     * Test calling closure from states
     */
    public function testCallingAfterGetClosure()
    {
        $closure = $this->_getProtectedClassObject()->getClosure(
            '_standardMethod6',
            $this->_getMockProxy(),
            States\StateInterface::VISIBILITY_PROTECTED
        );

        $this->assertInstanceOf('\UniAlteri\States\DI\InjectionClosureInterface', $closure);
        $this->assertInstanceOf('\Closure', $closure->getClosure());
        $this->assertEquals(3, call_user_func_array($closure->getClosure(), array(1, 2)));
    }

    /**
     * Test multiple call to getClosure for the same method
     */
    public function testGetMultipleSameClosures()
    {
        $projected = $this->_getProtectedClassObject();
        $closure1 = $projected->getClosure(
            '_standardMethod6',
            $this->_getMockProxy(),
            States\StateInterface::VISIBILITY_PROTECTED
        );

        $closure2 = $projected->getClosure(
            '_finalMethod7',
            $this->_getMockProxy(),
            States\StateInterface::VISIBILITY_PROTECTED
        );

        $closure3 = $projected->getClosure(
            '_standardMethod6',
            $this->_getMockProxy(),
            States\StateInterface::VISIBILITY_PROTECTED
        );

        $this->assertSame($closure1, $closure3);
        $this->assertSame($closure1->getClosure(), $closure3->getClosure());
        $this->assertNotSame($closure1, $closure2);
        $this->assertNotSame($closure1->getClosure(), $closure2->getClosure());
    }

    /**
     * Test multiple call to getClosure for the same method
     */
    public function testGetMultipleClosuresMultipleState()
    {
        $closure1 = $this->_getProtectedClassObject()->getClosure(
            '_standardMethod6',
            $this->_getMockProxy(),
            States\StateInterface::VISIBILITY_PROTECTED
        );

        $closure2 = $this->_getProtectedClassObject()->getClosure(
            '_finalMethod7',
            $this->_getMockProxy(),
            States\StateInterface::VISIBILITY_PROTECTED
        );

        $closure3 = $this->_getProtectedClassObject()->getClosure(
            '_standardMethod6',
            $this->_getMockProxy(),
            States\StateInterface::VISIBILITY_PROTECTED
        );

        $this->assertNotSame($closure1, $closure3);
        $this->assertNotSame($closure1->getClosure(), $closure3->getClosure());
        $this->assertNotSame($closure1, $closure2);
        $this->assertNotSame($closure1->getClosure(), $closure2->getClosure());
    }
}
