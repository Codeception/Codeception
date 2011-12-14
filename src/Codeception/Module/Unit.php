<?php
namespace Codeception\Module;

/**
 * Unit testing module
 *
 * This is the heart of CodeGuy testing framework.
 * By providing unique set of features Unit module makes your tests cleaner, readable, and easier to write.
 *
 * Here is example of unit test, which tests one of the methods of Codeception core.
 *
 * ``` php
 * <?php
 * class ScenarioCest
 * {
 *     public $class = '\Codeception\Scenario';
 *
 *     public function run(CodeGuy $I) {
 *         $I->wantTo('run steps from scenario');
 *
 *         // creating an empty stub:
 *         $I->haveFakeClass($test = Stub::makeEmpty('\Codeception\TestCase\Cept'));
 *
 *         // creating stub with defined properties
 *         $I->haveFakeClass($scenario = Stub::make('\Codeception\Scenario', array(
 *             'test' => $test,
 *             'steps' => array(
 *                Stub::makeEmpty('\Codeception\Step\Action'),
 *                Stub::makeEmpty('\Codeception\Step\Comment')
 *            )
 *        )));
 *
 *        // run the method we test on object specified
 *        $I->executeTestedMethodOn($scenario)
 *
 *        // perform assertions
 *            ->seeMethodInvoked($test,'runStep')
 *            ->seePropertyEquals($scenario, 'currentStep', 1);
 *    }
 *}
 * ```
 *
 * ## Features
 * * Descriptive - simply write what do you test and how do you test.
 * * Method execution limit - you are allowed only to execute tested method inside the scenario. Don't test several methods inside one unit.
 * * Simple stub definition - create stubbed class with one call. All properties and methods can be passed as callable functions.
 * * Dynamic mocking - stubs can be automatically turned to mocks.
 *
 * ## Unit Testing With Scenarios
 *
 * CodeGuy allows you to define testing scenarios in a natural way.
 * Typical test should consist of 3 steps, in this direct order:
 *
 * * environment definition
 * * method execution
 * * assertions
 *
 * That's the natural and logical way: we do something, and after that we check for result.
 *
 * In many cases in unit tests, you can't write your test in this order.
 * For example, if you use mocks, you should define the expected results before the tested method is executed.
 * Thus, the structure of test becomes harder for reading and understanding. Assertions can be put anywhere in test.
 *
 * Let's say we want to test method run for class Unit
 *
 * ``` php
 * <?php
 *
 * class Unit {
 *
 *      public function run()
 *      {
 *         $this->runExtra();
 *      }
 *
 *      protected function runExtra()
 *      {
 *      }
 *
 * }
 *
 * ```
 *
 * For PHPUnit our test will look like:
 *
 * ``` php
 * <?php
 *
 * $unit = $this->getMock('Unit', array('runExtra'));
 * $observer->expects($this->once())
 *   ->method('runExtra');
 *
 * $unit->run();
 * ```
 *
 * For assertion we use ->expects($this->once()) construction.
 * So, we have assertions that are assertions, and assertions that are expectations...
 * What a mess!
 *
 * Here, how the CodeGuy scenario looks like:
 *
 * ``` php
 * <?php
 *
 * $I = new CodeGuy($scenario);
 * $I->testMethod('Unit.run');
 * $I->haveStubClass($unit = Stub::make('Unit'));
 * $I->executeTestedMethodOn($unit);
 * $I->seeMethodInvoked($unit, 'runExtra');
 *
 * ```
 * So, that becomes much clearer.
 * We define stub (as environment) before the method execution.
 * We execute method.
 * We check the internal method was invoked.
 *
 * Also, we describe all our actions in proper and logical order.
 * Even junior developer can understand how and what you test here.
 *
 * But how is that possible?
 * How can we execute $unit->run() and see it triggered $unit->runExtra() afterwards?
 *
 * ## Simple Magic
 *
 * The magic inside of this module is behind the conecept of Codecept scenarios.
 * You describe your test in PHP DSL, then it's logged into scenario, and after that it's run step by step.
 * That's important. The test you are writing won't be executed as it is, in realtime.
 *
 * Unit module gets a benefit from it: it's methods can review next steps in scenarios and prepare mocks and exception handling.
 * Thus, the trick is: command $I->executeTestedMethod() doesn't actually execute it :)
 *
 */

class Unit extends \Codeception\Module
{

    protected $stubs = array();
    protected $predictedExceptions = array();
    protected $thrownExceptions = array();

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
        set_error_handler(function ($errno, $errstr, $errfile, $errline ) {
                    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
        );
    }

    public function _before(\Codeception\TestCase $test)
    {
        $this->test = $test;
        $this->stubs = array();
    }

    public function _after(\Codeception\TestCase $test)
    {
    }

    public function _failed(\Codeception\TestCase $test, $fail)
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
     *
     * ``` php
     * <?php
     * $I->testMethod('ClassName.MethodName'); // I will need ClassName instance for this
     * ```
     *
     * For static methods:
     *
     * ``` php
     * <?php
     * $I->testMethod('ClassName::MethodName');
     * ```
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
     * Adds a stub to internal registry.
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
     *
     * For non-static methods:
     *
     * ``` php
     * <?php
     * $I->executeTestedMethod($object, 1, 'hello', array(5,4,5));
     * ```
     *
     * The same for static method
     *
     * ``` php
     * <?php
     * $I->executeTestedMethod(1, 'hello', array(5,4,5));
     * ```
     *
     * @param $object null
     * @throws \InvalidArgumentException
     */
    /**
     * Alias for executeTestedMethod, only for non-static methods
     *
     * @alias executeTestedMethod
     * @param $object
     */
    public function executeTestedMethodOn($object)
    {
        call_user_func_array(array($this, 'executeTestedMethod'), func_get_args());
    }

    public function executeTestedMethodWith($params)
    {
        call_user_func_array(array($this, 'executeTestedMethod'), func_get_args());
    }

    public function executeTestedMethod($object = null)
    {
        // cleanup mocks
        foreach ($this->stubs as $mock) {
            $mock->__phpunit_cleanup();
        }

        $args = func_get_args();
        $this->predictExceptions();
        $res = null;
        if ($this->testedStatic) {

            if (!method_exists($this->testedClass, $this->testedMethod))
                throw new \Codeception\Exception\Module(__CLASS__,sprintf('%s::%s is not valid callable', $this->testedClass, $this->testedMethod));

            try {
                $res = call_user_func_array(array($this->testedClass, $this->testedMethod), $args);
            } catch (\Exception $e) {
                $this->catchException($e);
            }

            $this->debug("Static method {$this->testedClass}::{$this->testedMethod} executed");
            $this->debug('With parameters: ' . json_encode($args));
        } else {
            $obj = array_shift($args);

            $reflectedObj = new \ReflectionClass($obj);
            $reflectedMethod = $reflectedObj->getMethod($this->testedMethod);
            if (!$reflectedMethod)
                throw new \Codeception\Exception\Module(__CLASS__,sprintf('Method %s can\'t be called in this object', $this->testedMethod));

            if (!$reflectedMethod->isPublic()) {
                $reflectedMethod->setAccessible(true);
            }

            if (!$obj) throw new \InvalidArgumentException("Object for tested method is expected");
            if (isset($obj->__mocked)) $this->debug('Received Stub');
            $this->createMocks();
            try {
                $res = $reflectedMethod->invokeArgs($obj, $args);
            } catch (\Exception $e) {
                $this->catchException($e);
            }
            $this->debug("method {$this->testedMethod} executed");
        }
        $this->debug('Result: ' . json_encode($res));
        $this->last_result = $res;
    }

    protected  function catchException($e) {
        foreach ($this->predictedExceptions as $exception) {
            if ($e instanceof $exception) {
                $class = get_class($e);
                if (strpos($class,'\\')!== 0) $class = '\\'.$class;
                $this->thrownExceptions[$class] = $e;
                return;
            };
        }
        throw $e;
    }



    public function seeExceptionThrown($classname, $message = null) {

        \PHPUnit_Framework_Assert::assertContains($classname, array_keys($this->thrownExceptions));
        if ($message) {
            $e = $this->thrownExceptions[$classname];
            \PHPUnit_Framework_Assert::assertContains($message, $e->getMessage());
        }
    }

    protected function predictExceptions()
    {
        $this->thrownExceptions = array();
        $this->predictedExceptions = array();
        $scenario = $this->test->getScenario();
        $steps = $scenario->getSteps();
        for ($i = $scenario->getCurrentStep() + 1; $i < count($steps); $i++) {
            $step = $steps[$i];
            $action = $step->getAction();
            if ($action == 'executeTestedMethod') break;
            if ($action == 'executeTestedMethodOn') break;
            if ($action != 'seeExceptionThrown') continue;

            $args = $step->getArguments(false);
            $this->predictedExceptions[] = $args[0];
        }
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
        for ($i = $scenario->getCurrentStep()+1; $i < count($steps); $i++) {
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
            if ($step->getAction() == 'executeTestedMethod') break;
            if ($step->getAction() == 'executeTestedMethodOn') break;
            if ($step->getAction() == 'executeTestedMethodWith') break;
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