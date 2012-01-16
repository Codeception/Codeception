<?php
namespace Codeception\Module;

/**
 * Unit testing module
 *
 * This is the heart of CodeGuy testing framework.
 * By providing unique set of features Unit module makes your tests cleaner, readable, and easier to write.
 *
 * ## Features
 * * Descriptive - simply write what do you test and how do you test.
 * * Method execution limit - you are allowed only to execute tested method inside the scenario. Don't test several methods inside one unit.
 * * Simple stub definition - create stubbed class with one call. All properties and methods can be passed as callable functions.
 * * Dynamic mocking - stubs can be automatically turned to mocks.
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
     * Execute tested method on an object (stub can be passed).
     * First argument is an object, rest are supposed to be parameters passed to method.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->wantTo('authenticate user');
     * $I->testMethod('User.authenticate');
     * $user = new User();
     * $I->executeTestedMethodOn($user, 'Davert','qwerty');
     * // By this line $user->authenticate('Davert',''qwerty') was called.
     * $I->seeResultEquals(true);
     * ?>
     * ```
     *
     * For static methods use 'executeTestedMethodWith'.
     *
     * @param $object
     */
    public function executeTestedMethodOn($object)
    {
        $args = func_get_args();
        $obj = array_shift($args);

        $callable = function () use ($obj, $args) {

            $reflectedObj = new \ReflectionClass($obj);
            $reflectedMethod = $reflectedObj->getMethod($this->testedMethod);
            if (!$reflectedMethod)
                throw new \Codeception\Exception\Module(__CLASS__,sprintf('Method %s can\'t be called in this object', $this->testedMethod));

            if (!$reflectedMethod->isPublic()) {
                $reflectedMethod->setAccessible(true);
            }
            return $reflectedMethod->invokeArgs($obj, $args);
        };

        $this->createMocks();
        $this->execute($callable);
        if (isset($obj->__mocked)) $this->debug('Received Stub');
        $this->debug("Method {$this->testedMethod} executed");
        $this->debug('With parameters: ' . json_encode($args));

    }

    /**
     * Executes tested static method with parameters provided.
     *
     * ```
     * <?php
     * $I->testMethod('User::validateName');
     * $I->executeTestedMethodWith('davert',true);
     * // User::validate('davert', true); was called
     * ?>
     * ```
     * For non-static method use 'executeTestedMethodOn'
     *
     * @param $params
     * @throws \Codeception\Exception\Module
     */
    public function executeTestedMethodWith($params)
    {
        $args = func_get_args();
        if (!method_exists($this->testedClass, $this->testedMethod))
            throw new \Codeception\Exception\Module(__CLASS__,sprintf('%s::%s is not valid callable', $this->testedClass, $this->testedMethod));

        $callable = function () use ($args) {
            return call_user_func_array(array($this->testedClass, $this->testedMethod), $args);
        };

        $this->execute($callable);

        $this->debug("Static method {$this->testedClass}::{$this->testedMethod} executed");
        $this->debug('With parameters: ' . json_encode($args));
    }

    /**
     * Executes the method which is tested.
     * If method is not static, the class instance should be provided.
     *
     * If a method is static 'executeTestedWith' will be called.
     * If a method is not static 'executeTestedOn' will be called.
     * See those methods for the full reference
     *
     * @throws \InvalidArgumentException
     */
    public function executeTestedMethod()
    {
        $args = func_get_args();
        if ($this->testedStatic) {
            call_user_func_array(array($this, 'executeTestedMethodWith'), $args);
        } else {
            call_user_func_array(array($this, 'executeTestedMethodOn'), $args);
        }
    }


    /**
     * Executes a code block. The result of execution will be stored.
     * Parameter should be a valid Closure. The returned value can be checked with seeResult actions.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $user = new User();
     * $I->execute(function() use ($user) {
     *      $user->setName('Davert');
     *      return $user->getName();
     * });
     * $I->seeResultEquals('Davert');
     * ?>
     * ```
     *
     * You can use native PHPUnit asserts in executed code. This can be either static methods of PHPUnit_Framework_assert class,
     * or functions taken from 'PHPUnit/Framework/Assert/Functions.php'. They start with 'assert_' prefix.
     * You should manually include this file, as this functions may conflict with functions in your code.
     *
     * Example:
     *
     * ``` php
     * <?php
     * require_once 'PHPUnit/Framework/Assert/Functions.php';
     *
     * $user = new User();
     * $I->execute(function() use ($user) {
     *      $user->setName('Davert');
     *      assertEquals('Davert', $user->getName());
     * });
     * ```
     *
     * @param \Closure $code
     */
    public function execute(\Closure $code)
    {
        // cleanup mocks
        foreach ($this->stubs as $mock) {
            $mock->__phpunit_cleanup();
        }

        $this->predictExceptions();
        $res = null;

        try {
            $res = $code->__invoke();
        } catch (\Exception $e) {
            $this->catchException($e);
        }

        $this->debug('Result: ' . json_encode($res));
        $this->last_result = $res;
    }

    /**
     * Updates selected properties for object passed.
     * Can update even private and protected properties.
     *
     * @param $obj
     * @param array $values
     */

    public function changeProperties($obj, $values = array()) {
        $reflectedObj = new \ReflectionClass($obj);
            foreach ($values as $key => $val) {
                $property = $reflectedObj->getProperty($key);
                $property->setAccessible(true);
                $property->setValue($obj, $val);
            }

    }

    /**
     * Updates property of selected object
     * Can update even private and protected properties.
     *
     * @param $obj
     * @param $property
     * @param $value
     */

    public function changeProperty($obj, $property, $value) {
        $this->changeProperties($obj, array($property => $value));
    }

    public function seeExceptionThrown($classname, $message = null) {

        foreach ($this->thrownExceptions as $e) {
            if ($e instanceof $classname) {
                \PHPUnit_Framework_Assert::assertInstanceOf($classname, $e);
                if ($message) \PHPUnit_Framework_Assert::assertContains($message, $e->getMessage());
                return;
            }
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
            if ($action == 'executeTestedMethodWith') break;
            if ($action == 'execute') break;
            if ($action != 'seeExceptionThrown') continue;

            $args = $step->getArguments(false);
            $this->predictedExceptions[] = $args[0];
        }
    }

    protected function catchException($e)
    {
        $this->thrownExceptions[] = $e;
        foreach ($this->predictedExceptions as $predicted) {
            if ($e instanceof $predicted) return;
        }
        throw $e;
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
                        $params = $arguments;
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
            if ($step->getAction() == 'execute') break;
            if ($step->getAction() == 'executeTestedMethodOn') break;
            if ($step->getAction() == 'executeTestedMethodWith') break;
        }
    }

    /**
     * Checks the method of stub was invoked after the last execution.
     * Requires a stub as a first parameter, a method name as second.
     * Optionally pass an arguments which are expected for executed method.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->testMethod('UserService.create');
     * $I->haveStub($user = Stub::make('Model\User'));*
     * $service = new UserService($user);
     * $I->executeTestedMethodOn($service);
     * // we expect $user->save was invoked.
     * $I->seeMethodInvoked($user, 'save');
     * ?>
     * ```
     *
     * This method dynamically creates mock from stub.
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
     * Checks the method of stub was invoked *only once* after the last execution.
     * Requires a stub as a first parameter, a method name as second.
     * Optionally pass an arguments which are expected for executed method.
     *
     * Look for 'seeMethodInvoked' to see the example.

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
     * Checks the method of stub *was not invoked* after the last execution.
     * Requires a stub as a first parameter, a method name as second.
     * Optionally pass an arguments which are expected for executed method.

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
     * Checks the method of stub was invoked *only once* after the last execution.
     * Requires a stub as a first parameter, a method name as second and expected executions number.
     * Optionally pass an arguments which are expected for executed method.
     *
     * Look for 'seeMethodInvoked' to see the example.

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

    /**
     * Asserts that the last result from tested method is equal to value
     *
     * @param $value
     */
    public function seeResultEquals($value)
    {
        $this->assert(array('Equals', $value, $this->last_result,'in '.$this->last_result));
    }

    public function seeResultContains($value)
    {
        \PHPUnit_Framework_Assert::assertContains($value, $this->last_result);
    }

    /**
     * Checks the result of last execution doesn't contain a value passed.
     *
     * @param $value
     */
    public function dontSeeResultContains($value)
    {
        \PHPUnit_Framework_Assert::assertNotContains($value, $this->last_result);
    }

    /**
     * Checks result of last execution not equal to variable passed.
     *
     * @param $value
     */
    public function dontSeeResultEquals($value)
    {
        \PHPUnit_Framework_Assert::assertNotEquals($value, $this->last_result);
    }

    /**
     * Checks the result of last execution is empty.
     */
    public function seeEmptyResult()
    {
        \PHPUnit_Framework_Assert::assertEmpty($this->last_result);
    }

    /**
     * Checks result of last execution is of specific type.
     * Either 'int', 'bool', 'string', 'array', 'float', 'null', 'resource', 'scalar' can be passed for simple types.
     * Otherwise property will be checked to be an instance of type.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->execute(function() { return new User });
     * $I->seeResultIs('User');
     * ?>
     * ```
     *
     * @param $type
     */
    public function seeResultIs($type)
    {
        if (in_array($type, array('int', 'bool', 'string', 'array', 'float', 'null', 'resource', 'scalar'))) {
            return \PHPUnit_Framework_Assert::assertInternalType($type, $this->last_result);
        }
        \PHPUnit_Framework_Assert::assertInstanceOf($type, $this->last_result);
    }

    /**
     * Checks property of object equals to value provided.
     * Can check even protected or private properties.
     *
     * Consider testing hidden properties as a bad practice.
     * Use it if you have no other ways to test.
     *
     * @param $object
     * @param $property
     * @param $value
     */
    public function seePropertyEquals($object, $property, $value)
    {
        $current = $this->retrieveProperty($object, $property);
        $this->debug('Property value is: ' . $current);
        \PHPUnit_Framework_Assert::assertEquals($value, $current);
    }

    /**
     * Checks property is a passed type.
     * Either 'int', 'bool', 'string', 'array', 'float', 'null', 'resource', 'scalar' can be passed for simple types.
     * Otherwise property will be checked to be an instance of type.
     *
     * Consider testing hidden properties as a bad practice.
     * Use it if you have no other ways to test.
     *
     * @param $object
     * @param $property
     * @param $type
     */
    public function seePropertyIs($object, $property, $type) {
        $current = $this->retrieveProperty($object, $property);
        if (in_array($type, array('int', 'bool', 'string', 'array', 'float', 'null', 'resource', 'scalar'))) {
            return \PHPUnit_Framework_Assert::assertInternalType($type, $current);
        }
        \PHPUnit_Framework_Assert::assertInstanceOf($type, $current);
    }

    /**
     * Executes method and checks result is equal to passed value
     *
     * Example:
     *
     * ``` php
     * $I->testMethod('User.setName');
     * $user = new User();
     * $I->executeTestedMethodOn($user, 'davert');
     * $I->seeMethodResultEquals($user,'getName','davert');
     *
     * ```
     *     *
     * @param $object
     * @param $method
     * @param $value
     * @param array $params
     */
    public function seeMethodResultEquals($object, $method, $value, $params = array())
    {
        $result = call_user_func_array(array($object, $method), $params);
        \PHPUnit_Framework_Assert::assertEquals($value, $result);
    }

    /**
     * Executes method and checks result is of specified type.
     *
     * Either 'int', 'bool', 'string', 'array', 'float', 'null', 'resource', 'scalar' can be passed for simple types.
     * Otherwise property will be checked to be an instance of type.
     *
     * @param $object
     * @param $method
     * @param $type
     * @param array $params
     */
    public function seeMethodResultIs($object, $method, $type, $params = array())
    {
        $current = call_user_func_array(array($object, $method), $params);
        if (in_array($type, array('int', 'bool', 'string', 'array', 'float', 'null', 'resource', 'scalar'))) {
            return \PHPUnit_Framework_Assert::assertInternalType($type, $current);
        }
        \PHPUnit_Framework_Assert::assertInstanceOf($type, $current);
    }

    /**
     * Executes method and checks result is equal to passed value.
     *
     * Look for 'seeMethodResultEquals' for example.
     *
     * @param $object
     * @param $method
     * @param $value
     * @param array $params
     */
    public function dontSeeMethodResultEquals($object, $method, $value, $params = array())
    {
        $result = call_user_func_array(array($object, $method), $params);
        \PHPUnit_Framework_Assert::assertNotEquals($value, $result);
    }

    /**
     * Executes method and checks result is not of specified type.
     * Either 'int', 'bool', 'string', 'array', 'float', 'null', 'resource', 'scalar' can be passed for simple types.
     *
     * @param $object
     * @param $method
     * @param $type
     * @param array $params
     */
    public function seeMethodResultIsNot($object, $method, $type, $params = array())
    {
        $current = call_user_func_array(array($object, $method), $params);
        if (in_array($type, array('int', 'bool', 'string', 'array', 'float', 'null', 'resource', 'scalar'))) {
            return \PHPUnit_Framework_Assert::assertInternalType($type, $current);
        }
        \PHPUnit_Framework_Assert::assertNotInstanceOf($type, $current);
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