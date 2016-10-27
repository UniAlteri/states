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
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
namespace Teknoo\Tests\States\Proxy;

use Teknoo\States\Proxy;
use Teknoo\States\Proxy\Exception;
use Teknoo\States\State\StateInterface;
use Teknoo\Tests\Support\MockState1;
use Teknoo\Tests\Support\MockState2;
use Teknoo\Tests\Support\MockState3;

/**
 * Class AbstractProxyTest
 * Abstract tests case to test the excepted behavior of each proxy implementing the interface
 * Proxy\ProxyInterface.
 *
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Mock state 1, used in these tests.
     *
     * @var MockState1
     */
    protected $state1;

    /**
     * Mock state 2, used in these tests.
     *
     * @var MockState2
     */
    protected $state2;

    /**
     * Mock state 3, used in these tests.
     *
     * @var MockState3
     */
    protected $state3;

    /**
     * Proxy to test and validate.
     *
     * @var Proxy\ProxyInterface|Proxy\MagicCallTrait
     */
    protected $proxy;

    /**
     * Initialize objects for tests.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->state1 = new MockState1(false, 'my\Stated\Class');
        $this->state2 = new MockState2(false, 'my\Stated\Class');
        $this->state3 = new MockState3(false, 'my\Stated\Class');
        $this->buildProxy();
    }

    protected function tearDown()
    {
        $this->proxy = null;
        parent::tearDown();
    }

    /**
     * Build a proxy object, into $this->proxy to test it.
     *
     * @return Proxy\ProxyInterface
     */
    abstract protected function buildProxy();

    /**
     * Initialize proxy for test, register all states and enable one it.
     *
     * @param string $stateToEnable         to enable automatically into proxy
     * @param bool   $allowingMethodCalling : if state must
     */
    protected function initializeProxy($stateToEnable = null, $allowingMethodCalling = false)
    {
        if (empty($stateToEnable)) {
            $stateToEnable = MockState1::class;
        }

        $this->proxy->registerState(MockState1::class, $this->state1);
        $this->proxy->registerState(MockState2::class, $this->state2);
        $this->proxy->registerState(MockState3::class, $this->state3);
        $this->proxy->enableState($stateToEnable);

        switch ($stateToEnable) {
            case MockState1::class:
                if (true === $allowingMethodCalling) {
                    $this->state1->allowMethod();
                } else {
                    $this->state1->disallowMethod();
                }
                break;
            case MockState2::class:
                if (true === $allowingMethodCalling) {
                    $this->state2->allowMethod();
                } else {
                    $this->state2->disallowMethod();
                }
                break;
            case MockState3::class:
                if (true === $allowingMethodCalling) {
                    $this->state3->allowMethod();
                } else {
                    $this->state3->disallowMethod();
                }
                break;
        }
    }

    /**
     * Proxy must throw an exception if the registering state name is not a valid string.
     *
     * @expectedException \TypeError
     */
    public function testRegisterStateInvalidName()
    {
        $this->proxy->registerState(array(), $this->state1);
    }

    /**
     * Proxy must throw an exception if the registering state name is not a valid string.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\IllegalName
     */
    public function testRegisterStateBadName()
    {
        $this->proxy->registerState('', $this->state1);
    }

    /**
     * Proxy must throw an exception if the registering state name is not a valid string.
     *
     * @expectedException @expectedException \Teknoo\States\Proxy\Exception\StateNotFound
     */
    public function testRegisterStateBadClass()
    {
        $this->proxy->registerState('fooBar', $this->state1);
    }

    /**
     * Proxy must throw an exception if the registering state name is not a valid string.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\IllegalName
     */
    public function testRegisterStateClassNotImplementing()
    {
        $this->proxy->registerState(\DateTime::class, $this->state1);
    }

    /**
     * Check behavior of the proxy when we add a new state.
     */
    public function testRegisterStateWithInterface()
    {
        $this->proxy->registerState(StateInterface::class, $this->state1);
        self::assertEquals(array(StateInterface::class), $this->proxy->listAvailableStates());
    }

    /**
     * Check behavior of the proxy when we add a new state.
     */
    public function testRegisterState()
    {
        $this->proxy->registerState(MockState1::class, $this->state1);
        self::assertEquals(array(MockState1::class), $this->proxy->listAvailableStates());
    }

    /**
     * Proxy must throw an exception if the state name is not a valid string.
     *
     * @expectedException \TypeError
     */
    public function testUnRegisterStateInvalidName()
    {
        $this->proxy->unregisterState(array());
    }

    /**
     * Proxy must throw an exception if the state to remove is not registered.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\StateNotFound
     */
    public function testUnRegisterStateNonExistentState()
    {
        $this->proxy->unregisterState('NonExistentState');
    }

    /**
     * Proxy must throw an exception if the state name is not a valid string.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\StateNotFound
     */
    public function testUnRegisterStateClassExistStateNotFound()
    {
        $this->proxy->unregisterState(\DateTime::class);
    }

    /**
     * Test proxy behavior to unregister a state.
     */
    public function testUnRegisterState()
    {
        $this->initializeProxy();
        $this->proxy->unregisterState(MockState2::class);
        self::assertEquals(array(MockState1::class, MockState3::class), $this->proxy->listAvailableStates());
    }

    /**
     * Test proxy behavior to unregister an active state.
     */
    public function testUnRegisterEnableState()
    {
        $this->initializeProxy();
        $this->proxy->enableState(MockState3::class);
        self::assertEquals(array(MockState1::class, MockState3::class), $this->proxy->listEnabledStates());
        $this->proxy->unregisterState(MockState3::class);
        self::assertEquals(array(MockState1::class, MockState2::class), $this->proxy->listAvailableStates());
        self::assertEquals(array(MockState1::class), $this->proxy->listEnabledStates());
    }

    /**
     * Proxy must throw an exception if the state name is not a valid string in switch state method.
     *
     * @expectedException \TypeError
     */
    public function testSwitchStateInvalidName()
    {
        $this->proxy->switchState(array());
    }

    /**
     * Proxy must throw an exception if the state does not exist in switch state method.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\StateNotFound
     */
    public function testSwitchStateNonExistentName()
    {
        $this->proxy->switchState('NonExistentState');
    }

    /**
     * Test proxy behavior when we switch of states.
     */
    public function testSwitchState()
    {
        $this->initializeProxy();
        $this->proxy->switchState(MockState3::class);
        self::assertEquals(array(MockState3::class), $this->proxy->listEnabledStates());
    }

    /**
     * Test proxy behavior when we switch to already enable state.
     */
    public function testSwitchAlreadyLoadedState()
    {
        $this->initializeProxy();
        $this->proxy->enableState(MockState2::class);
        $this->proxy->switchState(MockState2::class);
        self::assertEquals(array(MockState2::class), $this->proxy->listEnabledStates());
    }

    /**
     * Proxy must throw an exception if the state name is not a valid string when we want enable a state.
     *
     * @expectedException \TypeError
     */
    public function testEnableStateInvalidName()
    {
        $this->proxy->enableState(array());
    }

    /**
     * Proxy must throw an exception if the state name is not a valid string.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\StateNotFound
     */
    public function testEnableStateClassExistStateNotFound()
    {
        $this->proxy->enableState(\DateTime::class);
    }

    /**
     * Proxy must throw an exception if the state is not available when we want enable a state.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\StateNotFound
     */
    public function testEnableStateNonExistentName()
    {
        $this->proxy->enableState('NonExistentState');
    }

    /**
     * Check proxy behavior when we enable a state.
     */
    public function testEnableState()
    {
        $this->initializeProxy();
        $this->proxy->disableState(MockState1::class);
        $this->proxy->enableState(MockState2::class);
        self::assertEquals(array(MockState2::class), $this->proxy->listEnabledStates());
    }

    /**
     * Check proxy behavior when we enable multiple states.
     */
    public function testEnableMultipleState()
    {
        $this->initializeProxy();
        $this->proxy->enableState(MockState2::class);
        self::assertEquals(array(MockState1::class, MockState2::class), $this->proxy->listEnabledStates());
    }

    /**
     * Proxy must throw an exception if the state name is not a valid string when we want disable a state.
     *
     * @expectedException \TypeError
     */
    public function testDisableStateInvalidName()
    {
        $this->proxy->disableState(array());
    }

    /**
     * Proxy must throw an exception if the state name is not a valid string.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\StateNotFound
     */
    public function testDisableStateClassExistStateNotFound()
    {
        $this->proxy->disableState(\DateTime::class);
    }

    /**
     * Proxy must throw an exception if the state is not available when we want enable a state.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\StateNotFound
     */
    public function testDisableStateNonExistentName()
    {
        $this->proxy->disableState('NonExistentState');
    }

    /**
     * Check proxy behavior when we disable a state.
     */
    public function testDisableState()
    {
        $this->initializeProxy();
        $this->proxy->enableState(MockState2::class);
        $this->proxy->disableState(MockState1::class);
        self::assertEquals(array(MockState2::class), $this->proxy->listEnabledStates());
    }

    /**
     * Check proxy behavior when we disable multiple states.
     */
    public function testDisableAllStates()
    {
        $this->initializeProxy();
        $this->proxy->enableState(MockState2::class);
        $this->proxy->disableAllStates();
        self::assertEquals(array(), $this->proxy->listEnabledStates());
    }

    /**
     * Check the proxy's method listAvailableStates behavior when there are no registered state.
     */
    public function testListAvailableStatesOfEmpty()
    {
        self::assertEquals(array(), $this->proxy->listAvailableStates());
    }

    /**
     * Check the proxy's method listAvailableStates behavior when there are no registered state.
     */
    public function testListAvailableStatesNotInit()
    {
        $proxyReflectionClass = new \ReflectionClass($this->proxy);
        $proxy = $proxyReflectionClass->newInstanceWithoutConstructor();
        self::assertEquals(array(), $proxy->listAvailableStates());
    }

    /**
     * Check the proxy's method listAvailableStates behavior.
     */
    public function testListAvailableStates()
    {
        $this->proxy->registerState(MockState1::class, $this->state1);
        $this->proxy->registerState(MockState3::class, $this->state3);
        self::assertEquals(array(MockState1::class, MockState3::class), $this->proxy->listAvailableStates());
    }

    /**
     * Check the proxy's method getStatesList behavior when there are no registered state.
     */
    public function testGetStatesListEmpty()
    {
        self::assertEmpty($this->proxy->getStatesList());
    }

    /**
     * Check the proxy's method getStatesList behavior when there are no registered state.
     */
    public function testGetStatesListNoInit()
    {
        $proxyReflectionClass = new \ReflectionClass($this->proxy);
        $proxy = $proxyReflectionClass->newInstanceWithoutConstructor();
        self::assertEmpty($proxy->getStatesList());
    }

    /**
     * Check the proxy's method getStatesList behavior.
     */
    public function testGetStatesList()
    {
        $this->proxy->registerState(MockState1::class, $this->state1);
        $this->proxy->registerState(MockState3::class, $this->state3);
        $statesList = $this->proxy->getStatesList();
        self::assertEquals(2, count($statesList));
        self::assertInstanceOf(StateInterface::class, $statesList[MockState1::class]);
        self::assertInstanceOf(StateInterface::class, $statesList[MockState3::class]);
    }

    /**
     * Check the proxy's method listEnabledStates behavior when there are no enable state.
     */
    public function testListEnabledStatesNotInit()
    {
        $proxyReflectionClass = new \ReflectionClass($this->proxy);
        $proxy = $proxyReflectionClass->newInstanceWithoutConstructor();
        self::assertEquals(array(), $proxy->listEnabledStates());
    }

    /**
     * Check the proxy's method listEnabledStates behavior when there are no enable state.
     */
    public function testListEnabledStatesOfEmpty()
    {
        $this->proxy->registerState(MockState1::class, $this->state1);
        $this->proxy->registerState(MockState3::class, $this->state3);
        self::assertEquals(array(), $this->proxy->listEnabledStates());
    }

    /**
     * Check the proxy's method listEnabledStates behavior.
     */
    public function testListEnabledStates()
    {
        $this->initializeProxy();
        self::assertEquals(array(MockState1::class), $this->proxy->listEnabledStates());
    }

    /**
     * Test behavior of the proxy when it was not initialized.
     */
    public function testInStateNotInitialized()
    {
        $proxyReflectionClass = new \ReflectionClass($this->buildProxy());
        $proxy = $proxyReflectionClass->newInstanceWithoutConstructor();
        self::assertFalse($proxy->inState(\DateTime::class));
    }

    /**
     * Test behavior of the proxy method inState.
     */
    public function testInState()
    {
        /*
         * @var Proxy\ProxyInterface
         */
        $proxy = $this->getMockObjectGenerator()->getMock(get_class($this->buildProxy()), array('listEnabledStates'), array(), '', false);
        $proxy->expects($this->any())
            ->method('listEnabledStates')
            ->withAnyParameters()
            ->willReturn(array(\DateTime::class, 'Bar'));

        self::assertFalse($proxy->inState(\stdClass::class));
        self::assertTrue($proxy->inState(\DateTime::class));
        $proxy = $this->buildProxy();
    }

    /**
     * Test proxy behavior when the called method name is not a string.
     *
     * @expectedException \TypeError
     */
    public function testCallInvalidName()
    {
        $this->proxy->__call(array(), array());
    }

    /**
     * Test proxy behavior when the required method is not implemented in anything active state.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\MethodNotImplemented
     */
    public function testCallNonImplementedWithoutState()
    {
        $this->proxy->test();
    }

    /**
     * Test proxy behavior when the required method is not implemented in the required state.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\MethodNotImplemented
     */
    public function testCallNonImplementedWithState()
    {
        $this->initializeProxy();
        $this->proxy->testOfState1();
    }

    /**
     * Test proxy behavior when the required method is implemented in several active state.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\AvailableSeveralMethodImplementations
     */
    public function testCallMultipleImplementation()
    {
        $this->initializeProxy();
        $this->proxy->enableState(MockState2::class);
        $this->state1->allowMethod();
        $this->state2->allowMethod();

        $this->proxy->test();
    }

    /**
     * Test proxy behavior in a normal calling.
     */
    public function testCall()
    {
        $this->initializeProxy(MockState1::class, true);
        $this->proxy->myCustomMethod('foo', 'bar');

        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('myCustomMethod', $this->state1->getMethodNameCalled());
        self::assertSame(array('foo', 'bar'), $this->state1->getCalledArguments());
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Following tests check if the proxy respects visibility restriction private/protected/public of called methods//
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Check proxy respects visibility restriction on methods from a function for :
     * - a private method : throw exception non implemented
     * - a protected method : throw exception non implemented
     * - a public method : return description if the method exists.
     */
    public function testCallFromFunction()
    {
        $this->initializeProxy(MockState1::class, true);
        //To access to the proxy in the method
        global $proxy;
        $proxy = $this->proxy;

        include_once dirname(dirname(__DIR__)).'/Support/TestVisibilityFunctionsCall.php';

        //Build temp functions to test proxy behavior with different scope visibility
        //from a function to get a description of a private method
        $fail = false;
        try {
            testCallFromFunctionPrivate();
        } catch (Exception\MethodNotImplemented $e) {
            $fail = true;
        } catch (\Exception $e) {
        }
        self::assertTrue($fail, 'It is a public scope, private methods are not available here');

        //Build temp functions to test proxy behavior with different scope visibility
        //from a function to get a description of a protected method
        $fail = false;
        try {
            testCallFromFunctionProtected();
        } catch (Exception\MethodNotImplemented $e) {
            $fail = true;
        } catch (\Exception $e) {
        }
        self::assertTrue($fail, 'It is a public scope, protected method are not available here');

        //Build temp functions to test proxy behavior with different scope visibility
        //from a function to get a description of a public method
        testCallFromFunctionPublic();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('publicTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Check proxy respects visibility restriction on methods from another object for :
     * - a private method : throw exception non implemented
     * - a protected method : throw exception non implemented
     * - a public method : return description if the method exists.
     */
    public function testCallFromOtherObject()
    {
        $this->initializeProxy(MockState1::class, true);
        //To access to the proxy in the method
        global $proxy;
        $proxy = $this->proxy;

        include_once dirname(dirname(__DIR__)).'/Support/TestVisibilityFunctionsCall.php';

        //Build temp functions to test proxy behavior with different scope visibility
        //from a external object to get a description of private methods
        $fail = false;
        try {
            $object = new \testCallFromOtherObject();
            $object->privateMethod();
        } catch (Exception\MethodNotImplemented $e) {
            $fail = true;
        } catch (\Exception $e) {
        }
        self::assertTrue($fail, 'It is a public scope, private methods are not available here');

        //Build temp functions to test proxy behavior with different scope visibility
        //from a external object to get a description of protected methods
        $fail = false;
        try {
            $object = new \testCallFromOtherObject();
            $object->protectedMethod();
        } catch (Exception\MethodNotImplemented $e) {
            $fail = true;
        } catch (\Exception $e) {
        }
        self::assertTrue($fail, 'It is a public scope, protected method are not available here');

        //Build temp functions to test proxy behavior with different scope visibility
        //from a external object to get a description of public methods
        $object = new \testCallFromOtherObject();
        $object->publicMethod();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('publicTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Check proxy respects visibility restriction on methods from an object of inherited class for :
     * - a private method : throw exception non implemented
     * - a protected method : return description if the method exists
     * - a public method : return description if the method exists.
     */
    public function testCallFromChildObject()
    {
        $this->initializeProxy(MockState1::class, true);
        //To access to the proxy in the method
        global $proxy;
        $proxy = $this->proxy;

        include_once dirname(dirname(__DIR__)).'/Support/TestVisibilityFunctionsCall.php';

        //Create a temp child class to test
        $classNamePartArray = explode('\\', get_class($this->proxy));
        $childClassName = array_pop($classNamePartArray);
        $childClassName = $childClassName.'ChildClass';
        $code = 'if (class_exists("'.$childClassName.'")) {return;}'.PHP_EOL.'class '.$childClassName.' extends '.get_class($this->proxy).'{ use testCallTrait; }';
        eval($code);

        //Build temp functions to test proxy behavior with different scope visibility
        //from a external object to get a description of private methods
        $fail = false;
        try {
            $object = new $childClassName();
            $object->privateMethod();
        } catch (Exception\MethodNotImplemented $e) {
            $fail = true;
        } catch (\Exception $e) {
        }
        self::assertTrue($fail, 'It is a public scope, private methods are not available here');

        //Build temp functions to test proxy behavior with different scope visibility
        //from a external object to get a description of protected methods
        $object = new $childClassName();
        $object->protectedMethod();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('protectedTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());

        //Build temp functions to test proxy behavior with different scope visibility
        //from a external object to get a description of public methods
        $object = new $childClassName();
        $object->publicMethod();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('publicTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Check proxy respects visibility restriction on methods from another object from the same class for :
     * - a private method : return description if the method exists
     * - a protected method : return description if the method exists
     * - a public method : return description if the method exists.
     */
    public function testCallFromOtherObjectSameClass()
    {
        $this->initializeProxy(MockState1::class, true);
        //To access to the proxy in the method
        include_once dirname(dirname(__DIR__)).'/Support/TestVisibilityFunctionsCall.php';

        //Create a temp child class to test
        $classNamePartArray = explode('\\', get_class($this->proxy));
        $childClassName = array_pop($classNamePartArray);
        $childClassName = $childClassName.'ChildClass';
        $code = 'if (class_exists("'.$childClassName.'")) {return;}'.PHP_EOL.'class '.$childClassName.' extends '.get_class($this->proxy).'{ use testCallTrait; }';
        eval($code);

        /*
         * In this test, use a child proxy and not directly the proxy because we can not add on the fly
         * method into the proxy to run the test
         */
        global $proxy;
        $proxy = new $childClassName();
        $proxy->registerState(MockState1::class, $this->state1);
        $proxy->registerState(MockState2::class, $this->state2);
        $proxy->registerState(MockState3::class, $this->state3);
        $proxy->enableState(MockState1::class);

        //Build temp functions to test proxy behavior with different scope visibility
        //from a same class object to get a description of private methods
        $proxy2 = new $childClassName();
        $proxy2->privateMethod();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('privateTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());

        //Build temp functions to test proxy behavior with different scope visibility
        //from a same class object to get a description of protected methods
        $proxy2->protectedMethod();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('protectedTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());

        //Build temp functions to test proxy behavior with different scope visibility
        //from a same class object to get a description of public methods
        $proxy2->publicMethod();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('publicTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Check proxy respects visibility restriction on methods from this object (called from one of its methods) for :
     * - a private method : return description if the method exists
     * - a protected method : return description if the method exists
     * - a public method : return description if the method exists.
     */
    public function testCallFromThis()
    {
        $this->initializeProxy(MockState1::class, true);
        //To access to the proxy in the method
        include_once dirname(dirname(__DIR__)).'/Support/TestVisibilityFunctionsCall.php';

        //Create a temp child class to test
        $classNamePartArray = explode('\\', get_class($this->proxy));
        $childClassName = array_pop($classNamePartArray);
        $childClassName = $childClassName.'ChildClass';
        $code = 'if (class_exists("'.$childClassName.'")) {return;}'.PHP_EOL.'class '.$childClassName.' extends '.get_class($this->proxy).'{ use testCallTrait; }';
        eval($code);

        /*
         * In this test, use a child proxy and not directly the proxy because we can not add on the fly
         * method into the proxy to run the test
         */
        global $proxy;
        $proxy = new $childClassName();
        $proxy->registerState(MockState1::class, $this->state1);
        $proxy->registerState(MockState2::class, $this->state2);
        $proxy->registerState(MockState3::class, $this->state3);
        $proxy->enableState(MockState1::class);

        //Build temp functions to test proxy behavior with different scope visibility
        //from $this to get a description of private methods
        $proxy->privateMethod();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('privateTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());

        //Build temp functions to test proxy behavior with different scope visibility
        //from $this to get a description of protected methods
        $proxy->protectedMethod();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('protectedTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());

        //Build temp functions to test proxy behavior with different scope visibility
        //from $this to get a description of public methods
        $proxy->publicMethod();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('publicTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Check proxy respects visibility restriction on methods from this object (called from one of its methods) for :
     * - a private method : return description if the method exists
     * - a protected method : return description if the method exists
     * - a public method : return description if the method exists.
     */
    public function testCallFromThisRecall()
    {
        $this->initializeProxy(MockState1::class, true);
        //To access to the proxy in the method
        include_once dirname(dirname(__DIR__)).'/Support/TestVisibilityFunctionsCall.php';

        //Create a temp child class to test
        $classNamePartArray = explode('\\', get_class($this->proxy));
        $childClassName = array_pop($classNamePartArray);
        $childClassName = $childClassName.'ChildClass';
        $code = 'if (class_exists("'.$childClassName.'")) {return;}'.PHP_EOL.'class '.$childClassName.' extends '.get_class($this->proxy).'{ use testCallTrait; }';
        eval($code);

        /*
         * In this test, use a child proxy and not directly the proxy because we can not add on the fly
         * method into the proxy to run the test
         */
        global $proxy;
        $proxy = new $childClassName();
        $proxy->registerState(MockState1::class, $this->state1);
        $proxy->registerState(MockState2::class, $this->state2);
        $proxy->registerState(MockState3::class, $this->state3);
        $proxy->enableState(MockState1::class);

        //Build temp functions to test proxy behavior with different scope visibility
        //from $this to get a description of private methods
        $proxy->recallMethod('privateMethod');
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('privateTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());

        //Build temp functions to test proxy behavior with different scope visibility
        //from $this to get a description of protected methods
        $proxy->recallMethod('protectedMethod');
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('protectedTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());

        //Build temp functions to test proxy behavior with different scope visibility
        //from $this to get a description of public methods
        $proxy->recallMethod('publicMethod');
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('publicTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Check proxy respects visibility restriction on methods from a static method of another class for :
     * - a private method : throw exception non implemented
     * - a protected method : throw exception non implemented
     * - a public method : return description if the method exists.
     */
    public function testCallFromStaticOtherClass()
    {
        $this->initializeProxy(MockState1::class, true);
        //To access to the proxy in the method
        global $proxy;
        $proxy = $this->proxy;

        include_once dirname(dirname(__DIR__)).'/Support/TestVisibilityFunctionsCall.php';

        //Build temp functions to test proxy behavior with different scope visibility
        //from a external class to get a description of private methods
        $fail = false;
        try {
            \testCallFromOtherObject::privateMethodStatic();
        } catch (Exception\MethodNotImplemented $e) {
            $fail = true;
        } catch (\Exception $e) {
        }
        self::assertTrue($fail, 'It is a public scope, private methods are not available here');

        //Build temp functions to test proxy behavior with different scope visibility
        //from a external class to get a description of protected methods
        $fail = false;
        try {
            \testCallFromOtherObject::protectedMethodStatic();
        } catch (Exception\MethodNotImplemented $e) {
            $fail = true;
        } catch (\Exception $e) {
        }
        self::assertTrue($fail, 'It is a public scope, protected method are not available here');

        //Build temp functions to test proxy behavior with different scope visibility
        //from a external class to get a description of public methods
        \testCallFromOtherObject::publicMethodStatic();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('publicTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Check proxy respects visibility restriction on methods from a static method of inherited class for :
     * - a private method : throw exception non implemented
     * - a protected method : return description if the method exists
     * - a public method : return description if the method exists.
     */
    public function testCallFromStaticChildClass()
    {
        $this->initializeProxy(MockState1::class, true);
        //To access to the proxy in the method
        global $proxy;
        $proxy = $this->proxy;

        include_once dirname(dirname(__DIR__)).'/Support/TestVisibilityFunctionsCall.php';

        //Create a temp child class to test
        $classNamePartArray = explode('\\', get_class($this->proxy));
        $childClassName = array_pop($classNamePartArray);
        $childClassName = $childClassName.'ChildClass';
        $code = 'if (class_exists("'.$childClassName.'")) {return;}'.PHP_EOL.'class '.$childClassName.' extends '.get_class($this->proxy).'{ use testCallTrait; }';
        eval($code);

        //Build temp functions to test proxy behavior with different scope visibility
        //from a external object to get a description of private methods
        $fail = false;
        try {
            $childClassName::privateMethodStatic();
        } catch (Exception\MethodNotImplemented $e) {
            $fail = true;
        } catch (\Exception $e) {
        }
        self::assertTrue($fail, 'It is a public scope, private methods are not available here');

        //Build temp functions to test proxy behavior with different scope visibility
        //from a external object to get a description of protected methods
        $childClassName::protectedMethodStatic();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('protectedTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());

        //Build temp functions to test proxy behavior with different scope visibility
        //from a external object to get a description of public methods
        $childClassName::publicMethodStatic();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('publicTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Check proxy respects visibility restriction on methods from a static method of the same class for :
     * - a private method : return description if the method exists
     * - a protected method : return description if the method exists
     * - a public method : return description if the method exists.
     */
    public function testCallFromStaticSameClass()
    {
        $this->initializeProxy(MockState1::class, true);
        //To access to the proxy in the method
        include_once dirname(dirname(__DIR__)).'/Support/TestVisibilityFunctionsCall.php';

        //Create a temp child class to test
        $classNamePartArray = explode('\\', get_class($this->proxy));
        $childClassName = array_pop($classNamePartArray);
        $childClassName = $childClassName.'ChildClass';
        $code = 'if (class_exists("'.$childClassName.'")) {return;}'.PHP_EOL.'class '.$childClassName.' extends '.get_class($this->proxy).'{ use testCallTrait; }';
        eval($code);

        /*
         * In this test, use a child proxy and not directly the proxy because we can not add on the fly
         * method into the proxy to run the test
         */
        global $proxy;
        $proxy = new $childClassName();
        $proxy->registerState(MockState1::class, $this->state1);
        $proxy->registerState(MockState2::class, $this->state2);
        $proxy->registerState(MockState3::class, $this->state3);
        $proxy->enableState(MockState1::class);

        //Build temp functions to test proxy behavior with different scope visibility
        //from a same class to get a description of private methods
        $childClassName::privateMethodStatic();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('privateTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());

        //Build temp functions to test proxy behavior with different scope visibility
        //from a same class to get a description of protected methods
        $childClassName::protectedMethodStatic();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('protectedTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());

        //Build temp functions to test proxy behavior with different scope visibility
        //from a same class to get a description of public methods
        $childClassName::publicMethodStatic();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('publicTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Check proxy respects visibility restriction on methods from a closure for :
     * - a private method : throw exception non implemented
     * - a protected method : throw exception non implemented
     * - a public method : return description if the method exists.
     */
    public function testCallFromClosure()
    {
        $this->initializeProxy(MockState1::class, true);
        //To access to the proxy in the method
        $proxy = $this->proxy;

        //Build temp functions to test proxy behavior with different scope visibility
        //from a closure to get a description of a private method
        $fail = false;
        try {
            $closure = function () use ($proxy) {
                return $proxy->privateTest();
            };
            $closure();
        } catch (Exception\MethodNotImplemented $e) {
            $fail = true;
        } catch (\Exception $e) {
        }
        self::assertTrue($fail, 'It is a public scope, private methods are not available here');

        //Build temp functions to test proxy behavior with different scope visibility
        //from a closure to get a description of a protected method
        $fail = false;
        try {
            $closure = function () use ($proxy) {
                return $proxy->protectedTest();
            };
            $closure();
        } catch (Exception\MethodNotImplemented $e) {
            $fail = true;
        } catch (\Exception $e) {
        }
        self::assertTrue($fail, 'It is a public scope, protected method are not available here');

        //Build temp functions to test proxy behavior with different scope visibility
        //from a closure to get a description of a public method
        $closure = function () use ($proxy) {
            return $proxy->publicTest();
        };
        $closure();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('publicTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Check proxy respects visibility restriction on methods from a closure bound with this current object for :
     * - a private method : return description if the method exists
     * - a protected method : return description if the method exists
     * - a public method : return description if the method exists.
     */
    public function testCallFromClosureBound()
    {
        $this->initializeProxy(MockState1::class, true);
        //To access to the proxy in the method
        $proxy = $this->proxy;

        //Build temp functions to test proxy behavior with different scope visibility
        //from a bound closure to get a description of a private method
        $closureOriginal = function () use ($proxy) {
            return $proxy->privateTest();
        };
        $closure = \Closure::bind($closureOriginal, $this->proxy);
        $closure();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('privateTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());

        //Build temp functions to test proxy behavior with different scope visibility
        //from a bound closure to get a description of a protected method
        $closureOriginal = function () use ($proxy) {
            return $proxy->protectedTest();
        };
        $closure = \Closure::bind($closureOriginal, $this->proxy);
        $closure();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('protectedTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());

        //Build temp functions to test proxy behavior with different scope visibility
        //from a bound closure to get a description of a public method
        $closureOriginal = function () use ($proxy) {
            return $proxy->publicTest();
        };
        $closure = \Closure::bind($closureOriginal, $this->proxy);
        $closure();
        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('publicTest', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // End tests for the method getMethodDescription in different visibility scope    //
    // For all other magics methods : PHP and interface define these methods as public//
    ////////////////////////////////////////////////////////////////////////////////////

    /**
     * Test exception behavior of the proxy when __invoke is not implemented into in actives states.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\MethodNotImplemented
     */
    public function testInvokeNonImplemented()
    {
        $this->initializeProxy();
        $proxy = $this->proxy;
        $proxy();
    }

    /**
     * Test proxy behavior with the magic method __invoke. If the call is forwarded to the active state.
     */
    public function testInvoke()
    {
        $this->initializeProxy(MockState1::class, true);
        $proxy = $this->proxy;
        $proxy('foo', 'bar');

        self::assertTrue($this->state1->methodWasCalled());
        self::assertEquals('invoke', $this->state1->getMethodNameCalled());
        self::assertEquals(array('foo', 'bar'), $this->state1->getCalledArguments());
    }

    /**
     * Test behavior of magic method during outside calling.
     */
    public function testGetIssetSetUnsetPublic()
    {
        //Test defined property
        self::assertEquals('value1', $this->proxy->publicProperty);
        self::assertTrue(isset($this->proxy->publicProperty));
        $this->proxy->publicProperty = 'value2';
        self::assertEquals('value2', $this->proxy->publicProperty);
        unset($this->proxy->publicProperty);
        self::assertFalse(isset($this->proxy->publicProperty));

        //Test missing property
        self::assertFalse(isset($this->proxy->missingPublicProperty));
        $fail = false;
        try {
            $a = $this->proxy->missingPublicProperty;
        } catch (\Throwable $e) {
            $fail = true;
        }
        if (false === $fail) {
            self::fail('Error __get must throw an exception for missing property');
        }

        $this->proxy->missingPublicProperty = 'fooBar';
        self::assertTrue(isset($this->proxy->missingPublicProperty));
        self::assertEquals('fooBar', $this->proxy->missingPublicProperty);
        unset($this->proxy->missingPublicProperty);
        self::assertFalse(isset($this->proxy->missingPublicProperty));

        $fail = false;
        try {
            $a = $this->proxy->missingPublicProperty;
        } catch (\Throwable $e) {
            $fail = true;
        }
        if (false === $fail) {
            self::fail('Error __get must throw an exception for missing property');
        }
    }

    /**
     * Test behavior of magic method during a state's method calling.
     */
    public function testGetIssetSetUnsetPublicByMethod()
    {
        //Test defined property
        $this->initializeProxy(MockState1::class, true);
        self::assertEquals('value1', $this->proxy->getPublicProperty());
        self::assertTrue($this->proxy->issetPublicProperty());
        $this->proxy->setPublicProperty('value2');
        self::assertEquals('value2', $this->proxy->getPublicProperty());
        $this->proxy->unsetPublicProperty();

        //Test missing property
        self::assertFalse($this->proxy->issetMissingPublicProperty());
        $fail = false;
        try {
            $a = $this->proxy->getOnMissingPublicProperty();
        } catch (\Throwable $e) {
            $fail = true;
        }
        if (false === $fail) {
            self::fail('Error __get must throw an exception for missing property');
        }

        $this->proxy->setOnMissingPublicProperty('fooBar');
        self::assertTrue($this->proxy->issetMissingPublicProperty());
        self::assertEquals('fooBar', $this->proxy->getOnMissingPublicProperty());
        $this->proxy->unsetOnMissingPublicProperty();
        self::assertFalse($this->proxy->issetMissingPublicProperty());
        $fail = false;
        try {
            $a = $this->proxy->getOnMissingPublicProperty();
        } catch (\Throwable $e) {
            $fail = true;
        }
        if (false === $fail) {
            self::fail('Error __get must throw an exception for missing property');
        }
    }

    /**
     * Test behavior of magic method during outside calling.
     *
     * @expectedException \Throwable
     */
    public function testGetProtectedGet()
    {
        self::assertEquals('value1', $this->proxy->protectedProperty);
    }

    /**
     * Test behavior of magic method during a state's method calling.
     */
    public function testIssetProtectedIsset()
    {
        self::assertFalse(isset($this->proxy->protectedProperty));
        self::assertFalse(isset($this->proxy->missingProtectedProperty));
    }

    /**
     * Test behavior of magic method during outside calling.
     *
     * @expectedException \Throwable
     */
    public function testSetProtected()
    {
        $this->proxy->protectedProperty = 'value2';
    }

    /**
     * Test behavior of magic method during a state's method calling.
     *
     * @expectedException \Throwable
     */
    public function testUnsetProtected()
    {
        unset($this->proxy->protectedProperty);
    }

    /**
     * Test behavior of magic method during outside calling.
     */
    public function testGetIssetSetUnsetProtectedViaMethod()
    {
        $this->initializeProxy(MockState1::class, true);
        self::assertEquals('value1', $this->proxy->getProProperty());
        self::assertTrue($this->proxy->issetProProperty());
        self::assertFalse($this->proxy->issetMissingProProperty());
        $this->proxy->setProProperty('value2');
        self::assertEquals('value2', $this->proxy->getProProperty());
        $this->proxy->unsetProProperty();
        self::assertFalse($this->proxy->issetProProperty());
    }

    /**
     * Test behavior of magic method during a state's method calling.
     *
     * @expectedException \Throwable
     */
    public function testGetPrivateGet()
    {
        self::assertEquals('value1', $this->proxy->privateProperty);
    }

    /**
     * Test behavior of magic method during outside calling.
     */
    public function testIssetPrivateIsset()
    {
        self::assertFalse(isset($this->proxy->privateProperty));
        self::assertFalse(isset($this->proxy->missingPrivateProperty));
    }

    /**
     * Test behavior of magic method during a state's method calling.
     *
     * @expectedException \Throwable
     */
    public function testSetUnsetPrivate()
    {
        $this->proxy->privateProperty = 'value2';
    }

    /**
     * Test behavior of magic method during outside calling.
     *
     * @expectedException \Throwable
     */
    public function testUnsetPrivate()
    {
        unset($this->proxy->privateProperty);
    }

    /**
     * Test behavior of magic method __call about a protected method during outside calling.
     */
    public function testCallPublicFromOutside()
    {
        self::assertEquals('fooBar', $this->proxy->publicMethodToCall());
    }

    /**
     * Test behavior of magic method __call about a protected method during outside calling.
     *
     * @expectedException \Throwable
     */
    public function testCallProtectedFromOutside()
    {
        $this->proxy->protectedMethodToCall();
    }

    /**
     * Test behavior of magic method __call about a protected method during outside calling.
     *
     * @expectedException \Throwable
     */
    public function testCallPrivateFromOutside()
    {
        $this->proxy->privateMethodToCall();
    }

    /**
     * Test behavior of magic method __call about a protected method during a state's method calling.
     */
    public function testCallPublicFromState()
    {
        $this->initializeProxy(MockState1::class, true);
        self::assertEquals('fooBar', $this->proxy->callPublicMethod());
    }

    /**
     * Test behavior of magic method __call about a protected method during outside calling.
     */
    public function testCallProtectedFromState()
    {
        $this->initializeProxy(MockState1::class, true);
        self::assertEquals('fooBar', $this->proxy->callProMethod());
    }

    /**
     * Test exception behavior of the proxy when __toString is not implemented into in actives states.
     */
    public function testToStringNonImplemented()
    {
        $this->initializeProxy();
        $s = 'error';
        try {
            $s = (string) $this->proxy;
        } catch (\Exception $e) {
            self::fail('Error, the proxy must throw an Exception\MethodNotImplemented exception when __toString is not implemented into in actives states');
        }

        self::assertEquals('', $s);
    }

    /**
     * Test proxy behavior with the magic method __toString. If the call is forwarded to the active state.
     */
    public function testToString()
    {
        $this->initializeProxy(MockState1::class, true);
        $s = (string) $this->proxy;

        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('toString', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Test exception behavior of the proxy when count is not implemented into in actives states.
     */
    public function testCountNonImplemented()
    {
        $this->initializeProxy();
        try {
            $this->proxy->count();
        } catch (Exception\MethodNotImplemented $e) {
            return;
        } catch (\Exception $e) {
        }

        self::fail('Error, the proxy must throw an Exception\MethodNotImplemented exception when count is not implemented into in actives states');
    }

    /**
     * Test proxy behavior with the magic method count. If the call is forwarded to the active state.
     */
    public function testCount()
    {
        $this->initializeProxy(MockState1::class, true);
        $this->proxy->count();

        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('count', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Test exception behavior of the proxy when offsetExist is not implemented into in actives states.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\MethodNotImplemented
     */
    public function testOffsetExistNonImplemented()
    {
        $this->initializeProxy();
        $a = isset($this->proxy[2]);
    }

    /**
     * Test proxy behavior with the magic method offsetExist. If the call is forwarded to the active state.
     */
    public function testOffsetExist()
    {
        $this->initializeProxy(MockState1::class, true);
        $a = isset($this->proxy[2]);

        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('offsetExists', $this->state1->getMethodNameCalled());
        self::assertSame(array(2), $this->state1->getCalledArguments());
    }

    /**
     * Test exception behavior of the proxy when offsetGet is not implemented into in actives states.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\MethodNotImplemented
     */
    public function testOffsetGetNonImplemented()
    {
        $this->initializeProxy();
        $value = $this->proxy[2];
    }

    /**
     * Test proxy behavior with the magic method offsetGet. If the call is forwarded to the active state.
     */
    public function testOffsetGet()
    {
        $this->initializeProxy(MockState1::class, true);
        $this->proxy[2];

        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('offsetGet', $this->state1->getMethodNameCalled());
        self::assertSame(array(2), $this->state1->getCalledArguments());
    }

    /**
     * Test exception behavior of the proxy when offsetSet is not implemented into in actives states.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\MethodNotImplemented
     */
    public function testOffsetSetNonImplemented()
    {
        $this->initializeProxy();
        $this->proxy[2] = 'foo';
    }

    /**
     * Test proxy behavior with the magic method offsetSet. If the call is forwarded to the active state.
     */
    public function testOffsetSet()
    {
        $this->initializeProxy(MockState1::class, true);
        $this->proxy[2] = 'foo';

        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('offsetSet', $this->state1->getMethodNameCalled());
        self::assertSame(array(2, 'foo'), $this->state1->getCalledArguments());
    }

    /**
     * Test exception behavior of the proxy when offsetUnset is not implemented into in actives states.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\MethodNotImplemented
     */
    public function testOffsetUnsetNonImplemented()
    {
        $this->initializeProxy();
        unset($this->proxy[2]);
    }

    /**
     * Test proxy behavior with the magic method offsetUnset. If the call is forwarded to the active state.
     */
    public function testOffsetUnset()
    {
        $this->initializeProxy(MockState1::class, true);
        unset($this->proxy[2]);

        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('offsetUnset', $this->state1->getMethodNameCalled());
        self::assertSame(array(2), $this->state1->getCalledArguments());
    }

    /**
     * Test exception behavior of the proxy when current is not implemented into in actives states.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\MethodNotImplemented
     */
    public function testCurrentNonImplemented()
    {
        $this->initializeProxy();
        $this->proxy->current();
    }

    /**
     * Test proxy behavior with the magic method current. If the call is forwarded to the active state.
     */
    public function testCurrent()
    {
        $this->initializeProxy(MockState1::class, true);
        $this->proxy->current();

        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('current', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Test exception behavior of the proxy when key is not implemented into in actives states.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\MethodNotImplemented
     */
    public function testKeyNonImplemented()
    {
        $this->initializeProxy();
        $this->proxy->key();
    }

    /**
     * Test proxy behavior with the magic method key. If the call is forwarded to the active state.
     */
    public function testKey()
    {
        $this->initializeProxy(MockState1::class, true);
        $this->proxy->key();

        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('key', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Test exception behavior of the proxy when next is not implemented into in actives states.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\MethodNotImplemented
     */
    public function testNextNonImplemented()
    {
        $this->initializeProxy();
        $this->proxy->next();
    }

    /**
     * Test proxy behavior with the magic method next. If the call is forwarded to the active state.
     */
    public function testNext()
    {
        $this->initializeProxy(MockState1::class, true);
        $this->proxy->next();

        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('next', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Test exception behavior of the proxy when rewind is not implemented into in actives states.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\MethodNotImplemented
     */
    public function testRewindNonImplemented()
    {
        $this->initializeProxy();
        $this->proxy->rewind();
    }

    /**
     * Test proxy behavior with the magic method rewind. If the call is forwarded to the active state.
     */
    public function testRewind()
    {
        $this->initializeProxy(MockState1::class, true);
        $this->proxy->rewind();

        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('rewind', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Test exception behavior of the proxy when seek is not implemented into in actives states.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\MethodNotImplemented
     */
    public function testSeekNonImplemented()
    {
        $this->initializeProxy();
        $this->proxy->seek(1);
    }

    /**
     * Test proxy behavior with the magic method seek. If the call is forwarded to the active state.
     */
    public function testSeek()
    {
        $this->initializeProxy(MockState1::class, true);
        $this->proxy->seek(2);

        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('seek', $this->state1->getMethodNameCalled());
        self::assertSame(array(2), $this->state1->getCalledArguments());
    }

    /**
     * Test exception behavior of the proxy when valid is not implemented into in actives states.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\MethodNotImplemented
     */
    public function testValidNonImplemented()
    {
        $this->initializeProxy();
        $this->proxy->valid();
    }

    /**
     * Test proxy behavior with the magic method valid. If the call is forwarded to the active state.
     */
    public function testValid()
    {
        $this->initializeProxy(MockState1::class, true);
        $this->proxy->valid();

        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('valid', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Test exception behavior of the proxy when getIterator is not implemented into in actives states.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\MethodNotImplemented
     */
    public function testGetIteratorNonImplemented()
    {
        $this->initializeProxy();
        $this->proxy->getIterator();
    }

    /**
     * Test proxy behavior with the magic method getIterator. If the call is forwarded to the active state.
     */
    public function testGetIterator()
    {
        $this->initializeProxy(MockState1::class, true);
        $iterator = new \ArrayIterator([1, 2, 3]);
        $this->state1->setClosure(function () use ($iterator) {
            return $iterator;
        });
        self::assertSame($iterator, $this->proxy->getIterator());
    }

    /**
     * Test exception behavior of the proxy when serialize is not implemented into in actives states.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\MethodNotImplemented
     */
    public function testSerializeNonImplemented()
    {
        $this->initializeProxy();
        serialize($this->proxy);
    }

    /**
     * Test proxy behavior with the magic method serialize. If the call is forwarded to the active state.
     */
    public function testSerialize()
    {
        $this->initializeProxy(MockState1::class, true);
        $this->proxy->serialize();

        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('serialize', $this->state1->getMethodNameCalled());
        self::assertSame(array(), $this->state1->getCalledArguments());
    }

    /**
     * Test exception behavior of the proxy when unserialize is not implemented into in actives states.
     *
     * @expectedException \Teknoo\States\Proxy\Exception\MethodNotImplemented
     */
    public function testUnSerializeNonImplemented()
    {
        $this->initializeProxy();
        $this->proxy->unserialize('');
    }

    /**
     * Test proxy behavior with the magic method unserialize. If the call is forwarded to the active state.
     */
    public function testUnSerialize()
    {
        $this->initializeProxy(MockState1::class, true);
        $this->proxy->unserialize('foo');

        self::assertTrue($this->state1->methodWasCalled());
        self::assertSame('unserialize', $this->state1->getMethodNameCalled());
        self::assertSame(array('foo'), $this->state1->getCalledArguments());
    }

    /**
     * Test the behavior of the proxy when it is cloned :
     * All states must be cloned
     * DI Container must be cloned
     * Registered states must be cloned
     * Active states must be cloned
     * The cloned proxy must has a new unique id.
     */
    public function testCloning()
    {
        $this->initializeProxy(MockState1::class, true);
        $obj = new \stdClass();
        $obj->foo = 'bar';
        $clonedProxy = clone $this->proxy;

        //States must be independently
        self::assertEquals(array(MockState1::class, MockState2::class, MockState3::class), $this->proxy->listAvailableStates());
        self::assertEquals(array(MockState1::class), $this->proxy->listEnabledStates());
        self::assertEquals(array(MockState1::class, MockState2::class, MockState3::class), $clonedProxy->listAvailableStates());
        self::assertEquals(array(MockState1::class), $clonedProxy->listEnabledStates());

        //List must perform independently
        $clonedProxy->switchState(MockState2::class);
        $clonedProxy->unregisterState(MockState3::class);
        self::assertEquals(array(MockState1::class, MockState2::class, MockState3::class), $this->proxy->listAvailableStates());
        self::assertEquals(array(MockState1::class), $this->proxy->listEnabledStates());
        self::assertEquals(array(MockState1::class, MockState2::class), $clonedProxy->listAvailableStates());
        self::assertEquals(array(MockState2::class), $clonedProxy->listEnabledStates());
    }

    /**
     * Test the behavior of the proxy when it is cloned :
     * All states must be cloned
     * DI Container must be cloned
     * Registered states must be cloned
     * Active states must be cloned
     * The cloned proxy must has a new unique id.
     */
    public function testCloningNonInitializeProxy()
    {
        $this->initializeProxy(MockState1::class, true);
        $reflectionClassProxyObject = new \ReflectionClass($this->proxy);
        $proxyNotInitialized = $reflectionClassProxyObject->newInstanceWithoutConstructor();
        try {
            $proxyCloned = clone $proxyNotInitialized;
        } catch (\Exception $e) {
            self::fail('Error, __clone must manage when the proxy was not initialized via the constructor');
        }
    }
}
