<?php
namespace Codeception\Util;

class Stub
{
    public static $magicMethods = array('__isset', '__get', '__set');

    /**
     * Instantiates a class without executing a constructor.
     * Properties and methods can be set as a second parameter.
     * Even protected and private properties can be set.
     *
     * ``` php
     * <?php
     * Stub::make('User');
     * Stub::make('User', array('name' => 'davert));
     * ?>
     * ```
     *
     * Accepts either name of class or object of that class
     *
     * ``` php
     * <?php
     * Stub::make(new User, array('name' => 'davert));
     * ?>
     * ```
     *
     * To replace method provide it's name as a key in second parameter and it's return value or callback function as parameter
     *
     * ``` php
     * <?php
     * Stub::make('User', array('save' => function () { return true; }));
     * Stub::make('User', array('save' => true }));
     * ?>
     * ```
     *
     * @param $class - A class to be mocked
     * @param array $params - properties and methods to set
     * @param bool|PHPUnit_Framework_TestCase $testCase
     * @return object - mock
     * @throws \RuntimeException when class not exists
     */
    public static function make($class, $params = array(), $testCase = false)
    {
        $class = self::getClassname($class);
        if (!class_exists($class)) throw new \RuntimeException("Stubbed class $class doesn't exist.");
        $reflection = new \ReflectionClass($class);

        $callables = self::getMethodsToReplace($reflection, $params);
        if (!empty($callables)) {
            if ($reflection->isAbstract()) {
                $mock = self::generateMockForAbstractClass($class, array_keys($callables), '', false, $testCase);
            } else {
                $mock = self::generateMock($class, array_keys($callables), array(), '', false, $testCase);
            }
        } else {
            if ($reflection->isAbstract()) {
                $mock = self::generateMockForAbstractClass($class, array(), '', false, $testCase);
            } else {
                $mock = self::generateMock($class, null, array(), '', false, $testCase);
            }
        }
        self::bindParameters($mock, $params);
        $mock->__mocked = $class;
        return $mock;
    }

    /**
     * Creates $num instances of class through `Stub::make`.
     *
     * @param $class
     * @param int $num
     * @param array $params
     * @return array
     */
    public static function factory($class, $num = 1, $params = array())
    {
        $objs = array();
        for ($i = 0; $i < $num; $i++) $objs[] = self::make($class, $params);
        return $objs;
    }

    /**
     * Instantiates class having all methods replaced with dummies except one.
     * Constructor is not triggered.
     * Properties and methods can be replaced.
     * Even protected and private properties can be set.
     *
     * ``` php
     * <?php
     * Stub::makeEmptyExcept('User', 'save');
     * Stub::makeEmptyExcept('User', 'save', array('name' => 'davert'));
     * ?>
     * ```
     *
     * Accepts either name of class or object of that class
     *
     * ``` php
     * <?php
     * * Stub::makeEmptyExcept(new User, 'save');
     * ?>
     * ```
     *
     * To replace method provide it's name as a key in second parameter and it's return value or callback function as parameter
     *
     * ``` php
     * <?php
     * Stub::makeEmptyExcept('User', 'save', array('isValid' => function () { return true; }));
     * Stub::makeEmptyExcept('User', 'save', array('isValid' => true }));
     * ?>
     * ```
     *
     * @param $class
     * @param $method
     * @param array $params
     * @param bool|PHPUnit_Framework_TestCase $testCase
     * @return object
     */
    public static function makeEmptyExcept($class, $method, $params = array(), $testCase = false)
    {
        $class = self::getClassname($class);
        $reflectionClass = new \ReflectionClass($class);
        $methods = $reflectionClass->getMethods();
        $methods = array_filter($methods, function($m) {
            return !in_array($m->name, Stub::$magicMethods);
        });
        $methods = array_filter($methods, function ($m) use ($method) {
            return $method != $m->name;
        });
        $methods = array_map(function ($m) {
            return $m->name;
        }, $methods);
        $methods = count($methods) ? $methods : null;
        $mock = self::generateMock($class, $methods, array(), '', false, $testCase);
        self::bindParameters($mock, $params);
        $mock->__mocked = $class;
        return $mock;
    }

    /**
     * Instantiates class having all methods replaced with dummies.
     * Constructor is not triggered.
     * Properties and methods can be set as a second parameter.
     * Even protected and private properties can be set.
     *
     * ``` php
     * <?php
     * Stub::makeEmpty('User');
     * Stub::makeEmpty('User', array('name' => 'davert));
     * ?>
     * ```
     *
     * Accepts either name of class or object of that class
     *
     * ``` php
     * <?php
     * Stub::makeEmpty(new User, array('name' => 'davert));
     * ?>
     * ```
     *
     * To replace method provide it's name as a key in second parameter and it's return value or callback function as parameter
     *
     * ``` php
     * <?php
     * Stub::makeEmpty('User', array('save' => function () { return true; }));
     * Stub::makeEmpty('User', array('save' => true }));
     * ?>
     * ```
     *
     * @param $class
     * @param array $params
     * @param bool|PHPUnit_Framework_TestCase $testCase
     * @return object
     */
    public static function makeEmpty($class, $params = array(), $testCase = false)
    {
        $class = self::getClassname($class);
        $methods = get_class_methods($class);
        $methods = array_filter($methods, function($i) {
            return !in_array($i, Stub::$magicMethods);
        });
        $mock = self::generateMock($class, $methods, array(), '', false, $testCase);
        self::bindParameters($mock, $params);
        $mock->__mocked = $class;
        return $mock;
    }

    /**
     * Clones an object and redefines it's properties (even protected and private)
     *
     * @param $obj
     * @param array $params
     * @return mixed
     */
    public static function copy($obj, $params = array())
    {
        $copy = clone($obj);
        self::bindParameters($copy, $params);
        return $copy;
    }

    /**
     * Instantiates a class instance by running constructor.
     * Parameters for constructor passed as second argument
     * Properties and methods can be set in third argument.
     * Even protected and private properties can be set.
     *
     * ``` php
     * <?php
     * Stub::construct('User', array('autosave' => false));
     * Stub::construct('User', array('autosave' => false), array('name' => 'davert));
     * ?>
     * ```
     *
     * Accepts either name of class or object of that class
     *
     * ``` php
     * <?php
     * Stub::construct(new User, array('autosave' => false), array('name' => 'davert));
     * ?>
     * ```
     *
     * To replace method provide it's name as a key in second parameter and it's return value or callback function as parameter
     *
     * ``` php
     * <?php
     * Stub::construct('User', array(), array('save' => function () { return true; }));
     * Stub::construct('User', array(), array('save' => true }));
     * ?>
     * ```
     *
     * @param $class
     * @param array $constructorParams
     * @param array $params
     * @param bool|PHPUnit_Framework_TestCase $testCase
     * @return object
     */
    public static function construct($class, $constructorParams = array(), $params = array(), $testCase = false)
    {
        $class = self::getClassname($class);
        $callables = self::getMethodsToReplace(new \ReflectionClass($class), $params);

        if (!empty($callables)) {
            $mock = self::generateMock($class, array_keys($callables), $constructorParams, $testCase);
        } else {
            $mock = self::generateMock($class, null, $constructorParams, $testCase);
        }
        self::bindParameters($mock, $params);
        $mock->__mocked = $class;
        return $mock;

    }

    /**
     * Instantiates a class instance by running constructor with all methods replaced with dummies.
     * Parameters for constructor passed as second argument
     * Properties and methods can be set in third argument.
     * Even protected and private properties can be set.
     *
     * ``` php
     * <?php
     * Stub::constructEmpty('User', array('autosave' => false));
     * Stub::constructEmpty('User', array('autosave' => false), array('name' => 'davert));
     * ?>
     * ```
     *
     * Accepts either name of class or object of that class
     *
     * ``` php
     * <?php
     * Stub::constructEmpty(new User, array('autosave' => false), array('name' => 'davert));
     * ?>
     * ```
     *
     * To replace method provide it's name as a key in second parameter and it's return value or callback function as parameter
     *
     * ``` php
     * <?php
     * Stub::constructEmpty('User', array(), array('save' => function () { return true; }));
     * Stub::constructEmpty('User', array(), array('save' => true }));
     * ?>
     * ```
     *
     * @param $class
     * @param array $constructorParams
     * @param array $params
     * @param bool|PHPUnit_Framework_TestCase $testCase
     * @return object
     */
    public static function constructEmpty($class, $constructorParams = array(), $params = array(), $testCase = false)
    {
        $class = self::getClassname($class);
        $methods = get_class_methods($class);
        $methods = array_filter($methods, function($i) {
            return !in_array($i, Stub::$magicMethods);
        });
        $mock = self::generateMock($class, $methods, $constructorParams, $testCase);
        self::bindParameters($mock, $params);
        $mock->__mocked = $class;
        return $mock;
    }

    /**
     * Instantiates a class instance by running constructor with all methods replaced with dummies, except one.
     * Parameters for constructor passed as second argument
     * Properties and methods can be set in third argument.
     * Even protected and private properties can be set.
     *
     * ``` php
     * <?php
     * Stub::constructEmptyExcept('User', 'save');
     * Stub::constructEmptyExcept('User', 'save', array('autosave' => false), array('name' => 'davert));
     * ?>
     * ```
     *
     * Accepts either name of class or object of that class
     *
     * ``` php
     * <?php
     * Stub::constructEmptyExcept(new User, 'save', array('autosave' => false), array('name' => 'davert));
     * ?>
     * ```
     *
     * To replace method provide it's name as a key in second parameter and it's return value or callback function as parameter
     *
     * ``` php
     * <?php
     * Stub::constructEmptyExcept('User', 'save', array(), array('save' => function () { return true; }));
     * Stub::constructEmptyExcept('User', 'save', array(), array('save' => true }));
     * ?>
     * ```
     *
     * @param $class
     * @param $method
     * @param array $constructorParams
     * @param array $params
     * @param bool|PHPUnit_Framework_TestCase $testCase
     * @return object
     */
    public static function constructEmptyExcept($class, $method, $constructorParams = array(), $params = array(), $testCase = false)
    {
        $class = self::getClassname($class);
        $reflectionClass = new \ReflectionClass($class);
        $methods = $reflectionClass->getMethods();
        $methods = array_filter($methods, function($m) {
            return !in_array($m->name, Stub::$magicMethods);
        });
        $methods = array_filter($methods, function ($m) use ($method) {
            return $method != $m->name;
        });
        $methods = array_map(function ($m) {
            return $m->name;
        }, $methods);
        $methods = count($methods) ? $methods : null;
        $mock = self::generateMock($class, $methods, $constructorParams, $testCase);
        self::bindParameters($mock, $params);
        $mock->__mocked = $class;
        return $mock;
    }

    private static function generateMock()
    {
        return self::doGenerateMock(func_get_args());
    }

    private static function generateMockForAbstractClass()
    {
      return self::doGenerateMock(func_get_args(), true);
    }

    private static function doGenerateMock($args, $isAbstract = false)
    {
      $testCase = self::extractTestCaseFromArgs($args);
      $generateMethod = $isAbstract ? 'getMockForAbstractClass' : 'getMock';

      if ($testCase instanceof \PHPUnit_Framework_TestCase)
        $mock = call_user_func_array(array($testCase, $generateMethod),$args);
      else
        $mock = call_user_func_array(array('\PHPUnit_Framework_MockObject_Generator', $generateMethod),$args);

      return $mock;
    }

    private static function extractTestCaseFromArgs(&$args)
    {
      $argsLength = count($args) - 1;
      $testCase = $args[$argsLength];

      unset($args[$argsLength]);

      return $testCase;
    }

    /**
     * Replaces properties and methods of current stub
     *
     * @param $mock
     * @param array $params
     * @return mixed
     * @throws \LogicException
     */
    public static function update($mock, array $params)
    {
        if (!$mock->__mocked) throw new \LogicException('You can update only stubbed objects');
        self::bindParameters($mock, $params);
        return $mock;
    }

    protected static function bindParameters($mock, $params)
    {
        $reflectionClass = new \ReflectionClass($mock);

        foreach ($params as $param => $value) {
            // redefine method
            if ($reflectionClass->hasMethod($param)) {
                if ($value instanceof StubMarshaler) {
                  $marshaler = $value;
                  $mock->
                      expects($marshaler->getMatcher())->
                      method($param)->
                      will(new \PHPUnit_Framework_MockObject_Stub_ReturnCallback($marshaler->getValue()));
                } elseif ($value instanceof \Closure) {
                    $mock->
                        expects(new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)->
                        method($param)->
                        will(new \PHPUnit_Framework_MockObject_Stub_ReturnCallback($value));
                } else {
                    $mock->
                        expects(new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)->
                        method($param)->
                        will(new \PHPUnit_Framework_MockObject_Stub_Return($value));
                }
            } elseif ($reflectionClass->hasProperty($param)) {
                $reflectionProperty = $reflectionClass->getProperty($param);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($mock, $value);
                continue;
            } else {
                $mock->$param = $value;
                continue;
            }

        }
    }

    protected static function getClassname($object)
    {
        if (is_object($object)) return get_class($object);
        if (is_callable($object)) return call_user_func($object);
        return $object;
    }

    protected static function getMethodsToReplace($reflection, $params)
    {
        $callables = array();
        foreach ($params as $method => $value) {
            if ($reflection->hasMethod($method)) $callables[$method] = $value;
        }
        return $callables;
    }

    /**
     * Checks if a method never has been invoked
     *
     * If method invoked, it will immediately throw an
     * exception.
     *
     * ``` php
     * <?php
     * $user = Stub::make('User', array('getName' => Stub::never(), 'someMethod' => function() {}));
     * $user->someMethod();
     * ?>
     * ```
     *
     * @param mixed $params
     * @return StubMarshaler
     */
    public static function never($params = null) {
      return new StubMarshaler(new \PHPUnit_Framework_MockObject_Matcher_InvokedCount(0), self::closureIfNull($params));
    }

    /**
     * Checks if a method has been invoked exactly one
     * time.
     *
     * If the number is less or greater it will later be checked in verify() and also throw an
     * exception.
     *
     * ``` php
     * <?php
     * $user = Stub::make('User', array('getName' => Stub::once(function() { return 'Davert';}), 'someMethod' => function() {}));
     * $userName = $user->getName();
     * $this->assertEquals('Davert', $userName);
     * ?>
     * ```
     *
     * @param mixed $params
     * @return StubMarshaler
     */
    public static function once($params = null) {
      return new StubMarshaler(new \PHPUnit_Framework_MockObject_Matcher_InvokedCount(1), self::closureIfNull($params));
    }

    /**
     * Checks if a method has been invoked at least one
     * time.
     *
     * If the number of invocations is 0 it will throw an exception in verify.
     *
     * ``` php
     * <?php
     * $user = Stub::make('User', array('getName' => Stub::atLeastOnce(function() { return 'Davert';}), 'someMethod' => function() {}));
     * $user->getName();
     * $user->getName();
     * ?>
     * ```
     *
     * @param mixed $params
     * @return StubMarshaler
     */
    public static function atLeastOnce($params = null) {
      return new StubMarshaler(new \PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce, self::closureIfNull($params));
    }

    /**
     * Checks if a method has been invoked a certain amount
     * of times.
     * If the number of invocations exceeds the value it will immediately throw an
     * exception,
     * If the number is less it will later be checked in verify() and also throw an
     * exception.
     *
     * ``` php
     * <?php
     * $user = Stub::make('User', array('getName' => Stub::exactly(3, function() { return 'Davert';}), 'someMethod' => function() {}));
     * $user->getName();
     * $user->getName();
     * $user->getName();
     * ?>
     * ```
     *
     * @param mixed $params
     * @return StubMarshaler
     */
    public static function exactly($count, $params = null) {
      return new StubMarshaler(new \PHPUnit_Framework_MockObject_Matcher_InvokedCount($count), self::closureIfNull($params));
    }

    private static function closureIfNull($params) {
      return null == $params ? function() {} : $params;
  }

}

/**
 * Holds matcher and value of mocked method
 */

class StubMarshaler
{
    private $methodMatcher;

    private $methodValue;

    public function __construct(\PHPUnit_Framework_MockObject_Matcher_InvokedRecorder $matcher, $value)
    {
        $this->methodMatcher = $matcher;
        $this->methodValue = $value;
    }

    public function getMatcher()
    {
        return $this->methodMatcher;
    }

    public function getValue()
    {
        return $this->methodValue;
    }
}