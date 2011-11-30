<?php
namespace Codeception\Module;

/**
 * Unit testing module
 *
 *
 *
 */

class Unit extends \Codeception\Module
{

    protected $stubs = array();

    protected $last_result;

    /**
     * @var \Codeception\TestCase
     */
    protected $test;

    protected $testedClass;
    protected $testedMethod;

    protected $testedStatic;

    public function _initialize()
    {
        \Codeception\Util\Stub\Builder::loadClasses(); // loading stub classes
    }

    public function _before(\PHPUnit_Framework_TestCase $test)
    {
        $this->test = $test;
        $this->stubs = array();
        set_error_handler(function ($errno, $errstr, $errfile, $errline)
        {
            throw new \Codeception\Exception\TestRuntime($errstr, $errno);
        });
    }

    public function __after()
    {
        restore_error_handler();
    }

    public function _failed()
    {
        if (count($this->stubs)) {
            $this->debug("Stubs were used:");
            foreach ($this->stubs as $stub) {
                if (isset($stub->__mocked)) $this->debug($stub->__mocked);
                $this->debug(json_encode($stub));
            }
        }
    }

    /**
     * Registers a class/method which will be tested.
     * When you run 'execute' this method will be invoked.
     * Please, not that it also update the feature section of scenario.
     *
     * For non-static methods:
     * ````
     * testMethod('ClassName.MethodName')
     * ````
     *
     * For static methods:
     * ````
     * testMethod('ClassName::MethodName')
     * ````
     *
     * @param $signature
     */
    public function testMethod($signature)
    {
        if (strpos($signature, '.')) {
            // this is class method
            list($class, $method) = explode('.', $signature);
            $this->testedClass = $class;
            $this->testedMethod = $method;
            $this->testedStatic = false;
        } elseif (strpos($signature, '::')) {
            // we test static method
            list($class, $method) = explode('::', $signature);
            $this->testedClass = $class;
            $this->testedMethod = $method;
            $this->testedStatic = true;
        }
        $this->debug('Class: ' . $class);
        $this->debug('Method: ' . $method);
        if ($this->testedStatic) $this->debug('Static');
    }

    /**
     * Adds stub in internal registry.
     * Use this command if you need to convert this stub to mock.
     * Without adding stub to registry you can't trace it's method invocations.
     *
     * @param $instance
     */
    public function haveFakeClass($instance)
    {
        $this->stubs[] = $instance;
        $stubid = count($this->stubs) - 1;
        if (isset($instance->__mocked)) $this->debugSection('Registered stub', 'Stub_' . $stubid . ' {' . $instance->__mocked . '}');
    }

    /**
     * Alias for haveFakeClass
     *
     * @alias haveFakeClass
     * @param $instance
     */
    public function haveStub($instance)
    {
        $this->haveFakeClass($instance);
    }


    /**
     * Executes the method which is tested.
     * If method is not static, the class instance should be provided.
     * Otherwise bypass the first parameter blank
     *
     * Include additional arguments as parameter.
     *
     * Examples:
     * For non-static methods:
     * ````
     * executeTestedMethod($object, 1, 'hello', array(5,4,5));
     * ````
     *
     * The same for static method
     * ```
     * executeTestedMethod(1, 'hello', array(5,4,5));
     * ```
     *
     * @param $object null
     * @throws \InvalidArgumentException
     */
    public function executeTestedMethod($object = null)
    {
        $args = func_get_args();
        if ($this->testedStatic) {
            $res = call_user_func_array(array($this->testedClass, $this->testedMethod), $args);
            $this->debug("Static method {$this->testedClass}::{$this->testedMethod} executed");
            $this->debug('With parameters: ' . json_encode($args));
        } else {
            $obj = array_shift($args);
            if (isset($obj->__mocked)) $this->debug('Received STUB');
            $this->createMocks();
            if (!$obj) throw new \InvalidArgumentException("Object for tested method is expected");
            $res = call_user_func_array(array($obj, $this->testedMethod), $args);
            $this->debug("method {$this->testedMethod} executed");
        }
        $this->debug('Result: ' . json_encode($res));
        $this->last_result = $res;
    }

    /**
     * Alias for executeTestedMethod, only for non-static methods
     *
     * @alias executeTestedMethod
     * @param $object
     */
    public function executeTestedMethodOn($object)
    {
        $this->executeTestedMethod($object);
    }

    /**
     * Very magical function that generates Mock methods for expected assertions
     * Allows declare seeMethodInvoked, seeMethodNotInvoked, etc AFTER the 'execute' command
     *
     */
    protected function createMocks()
    {
        $scenario = $this->test->getScenario();
        $scenario->getCurrentStep();
        $steps = $scenario->getSteps();
        for ($i = $scenario->getCurrentStep(); $i < count($steps); $i++) {
            $step = $steps[$i];
            if (strpos($action = $step->getAction(), 'seeMethod') === 0) {
                $arguments = $step->getArguments(false);
                $mock = array_shift($arguments);
                $function = array_shift($arguments);
                $params = array_shift($arguments);

                $invoke = false;

                switch ($action) {
                    case 'seeMethodInvoked':
                    case 'seeMethodInvokedAtLeastOnce':
                        if (!$mock) throw new \InvalidArgumentException("Stub class not defined");
                        $invoke = new \PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce();
                        break;
                    case 'seeMethodInvokedOnce':
                        if (!$mock) throw new \InvalidArgumentException("Stub class not defined");
                        $invoke = new \PHPUnit_Framework_MockObject_Matcher_InvokedCount(1);
                        break;
                    case 'seeMethodNotInvoked':
                        if (!$mock) throw new \InvalidArgumentException("Stub class not defined");
                        $invoke = new \PHPUnit_Framework_MockObject_Matcher_InvokedCount(0);
                        break;
                    case 'seeMethodInvokedMultipleTimes':
                        if (!$mock) throw new \InvalidArgumentException("Stub class not defined");
                        $times = $params;
                        if (!is_int($times)) throw new \InvalidArgumentException("Invoked times count should be an integer");
                        $params = array_shift($arguments);
                        $invoke = new \PHPUnit_Framework_MockObject_Matcher_InvokedCount($times);
                        break;
                    default:
                }

                if ($invoke) {
                    $mockMethod = $mock->expects($invoke)->method($function);
                    $this->debug(get_class($invoke) . ' attached');
                    if ($params) {
                        call_user_func_array(array($mockMethod, 'with'), $params);
                        $this->debug('with ' . json_encode($params));
                    }
                }


            }
            if ($steps[$i]->getAction() == 'execute') break;
        }
    }

    /**
     *
     *
     * @magic
     * @see createMocks
     * @param $mock
     * @param $method
     * @param array $params
     */
    public function seeMethodInvoked($mock, $method, array $params = array())
    {
        $this->verifyMock($mock);
    }

    /**
     *
     * @magic
     * @see createMocks
     * @param $mock
     * @param $method
     * @param array $params
     */
    public function seeMethodInvokedOnce($mock, $method, array $params = array())
    {
        $this->verifyMock($mock);
    }

    /**
     *
     * @magic
     * @see createMocks
     * @param $mock
     * @param $method
     * @param array $params
     */
    public function seeMethodNotInvoked($mock, $method, array $params = array())
    {
        $this->verifyMock($mock);
    }

    /**
     *
     * @magic
     * @see createMocks
     * @param $mock
     * @param $method
     * @param $times
     * @param array $params
     */
    public function seeMethodInvokedMultipleTimes($mock, $method, $times, array $params = array())
    {
        $this->verifyMock($mock);
    }

    protected function verifyMock($mock)
    {
        foreach ($this->stubs as $stubid => $stub) {
            if (spl_object_hash($stub) == spl_object_hash($mock)) {
                if (!$mock->__phpunit_hasMatchers()) {
                    throw new \Exception("Probably Internal Error. There is no matchers for current mock");
                }
                if (isset($stub->__mocked)) {
                    $this->debugSection('Triggered Stub', 'Stub_' . $stubid . ' {' . $stub->__mocked . '}');
                }

                \PHPUnit_Framework_Assert::assertTrue(true); // hook to increment assertions counter
                try {
                    $mock->__phpunit_verify();
                } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                    \PHPUnit_Framework_Assert::fail("\n" . $e->getMessage()); // hook to increment assertions counter
                    throw $e;
                }
                $mock->__phpunit_cleanup();
                return;
            }
        }
        throw new \Exception("Mock is not registered by 'haveStub' or 'haveFakeClass' methods");
    }

    public function seeResultEquals($value)
    {
        $this->assert(array('Equals', $value, $this->last_result));
    }

    public function seeResultContains($value)
    {
        \PHPUnit_Framework_Assert::assertContains($value, $this->last_result);
    }

    public function dontSeeResultContains($value)
    {
        \PHPUnit_Framework_Assert::assertNotContains($value, $this->last_result);
    }

    public function seeResultNotEquals($value)
    {
        \PHPUnit_Framework_Assert::assertNotEquals($value, $this->last_result);
    }

    public function seeEmptyResult()
    {
        \PHPUnit_Framework_Assert::assertEmpty($this->last_result);
    }

    public function seeResultIs($type)
    {
        if (in_array($type, array('int', 'bool', 'string', 'array', 'float', 'null', 'resource', 'scalar'))) {
            return \PHPUnit_Framework_Assert::assertInternalType($type, $this->last_result);
        }
        return \PHPUnit_Framework_Assert::assertInstanceOf($type, $this->last_result);
    }

    public function seePropertyEquals($object, $property, $value)
    {
        $current = $this->retrieveProperty($object, $property);
        $this->debug('Property value is: ' . $current);
        \PHPUnit_Framework_Assert::assertEquals($value, $current);
    }

    protected function retrieveProperty($object, $property)
    {
        if (isset($object->__mocked)) $this->debug('Received STUB');
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($object);
    }


}