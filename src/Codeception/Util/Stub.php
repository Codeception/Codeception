<?php

namespace Codeception\Util;

class Stub
{
    public static $magicMethods = ['__isset', '__get', '__set'];

    /**
     * Instantiates a class without executing a constructor.
     * Properties and methods can be set as a second parameter.
     * Even protected and private properties can be set.
     *
     * ``` php
     * <?php
     * Stub::make('User');
     * Stub::make('User', array('name' => 'davert'));
     * ?>
     * ```
     *
     * Accepts either name of class or object of that class
     *
     * ``` php
     * <?php
     * Stub::make(new User, array('name' => 'davert'));
     * ?>
     * ```
     *
     * To replace method provide it's name as a key in second parameter
     * and it's return value or callback function as parameter
     *
     * ``` php
     * <?php
     * Stub::make('User', array('save' => function () { return true; }));
     * Stub::make('User', array('save' => true }));
     * ?>
     * ```
     *
     * @param mixed $class - A class to be mocked
     * @param array $params - properties and methods to set
     * @param bool|\PHPUnit_Framework_TestCase $testCase
     *
     * @return object - mock
     * @throws \RuntimeException when class does not exist
     */
    public static function make($class, $params = [], $testCase = false)
    {
        $class = self::getClassname($class);
        if (!class_exists($class)) {
            if (interface_exists($class)) {
                throw new \RuntimeException("Stub::make can't mock interfaces, please use Stub::makeEmpty instead.");
            }
            throw new \RuntimeException("Stubbed class $class doesn't exist.");
        }

        $reflection = new \ReflectionClass($class);
        $callables = self::getMethodsToReplace($reflection, $params);
        if ($reflection->isAbstract()) {
            $arguments = empty($callables) ? [] : array_keys($callables);
            $mock = self::generateMockForAbstractClass($class, $arguments, '', false, $testCase);
        } else {
            $arguments = empty($callables) ? null : array_keys($callables);
            $mock = self::generateMock($class, $arguments, [], '', false, $testCase);
        }

        self::bindParameters($mock, $params);

        return self::markAsMock($mock, $reflection);
    }

    /**
     * Set __mock flag, if at all possible
     *
     * @param object $mock
     * @param \ReflectionClass $reflection
     * @return object
     */
    private static function markAsMock($mock, \ReflectionClass $reflection)
    {
        if (!$reflection->hasMethod('__set')) {
            $mock->__mocked = $reflection->getName();
        }
        return $mock;
    }

    /**
     * Creates $num instances of class through `Stub::make`.
     *
     * @param mixed $class
     * @param int $num
     * @param array $params
     *
     * @return array
     */
    public static function factory($class, $num = 1, $params = [])
    {
        $objects = [];
        for ($i = 0; $i < $num; $i++) {
            $objects[] = self::make($class, $params);
        }

        return $objects;
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
     * To replace method provide it's name as a key in second parameter
     * and it's return value or callback function as parameter
     *
     * ``` php
     * <?php
     * Stub::makeEmptyExcept('User', 'save', array('isValid' => function () { return true; }));
     * Stub::makeEmptyExcept('User', 'save', array('isValid' => true }));
     * ?>
     * ```
     *
     * @param mixed $class
     * @param string $method
     * @param array $params
     * @param bool|\PHPUnit_Framework_TestCase $testCase
     *
     * @return object
     */
    public static function makeEmptyExcept($class, $method, $params = [], $testCase = false)
    {
        $class = self::getClassname($class);
        $reflectionClass = new \ReflectionClass($class);

        $methods = $reflectionClass->getMethods();

        $methods = array_filter(
            $methods,
            function ($m) {
                return !in_array($m->name, Stub::$magicMethods);
            }
        );

        $methods = array_filter(
            $methods,
            function ($m) use ($method) {
                return $method != $m->name;
            }
        );

        $methods = array_map(
            function ($m) {
                return $m->name;
            },
            $methods
        );

        $methods = count($methods) ? $methods : null;
        $mock = self::generateMock($class, $methods, [], '', false, $testCase);
        self::bindParameters($mock, $params);

        return self::markAsMock($mock, $reflectionClass);
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
     * Stub::makeEmpty('User', array('name' => 'davert'));
     * ?>
     * ```
     *
     * Accepts either name of class or object of that class
     *
     * ``` php
     * <?php
     * Stub::makeEmpty(new User, array('name' => 'davert'));
     * ?>
     * ```
     *
     * To replace method provide it's name as a key in second parameter
     * and it's return value or callback function as parameter
     *
     * ``` php
     * <?php
     * Stub::makeEmpty('User', array('save' => function () { return true; }));
     * Stub::makeEmpty('User', array('save' => true }));
     * ?>
     * ```
     *
     * @param mixed $class
     * @param array $params
     * @param bool|\PHPUnit_Framework_TestCase $testCase
     *
     * @return object
     */
    public static function makeEmpty($class, $params = [], $testCase = false)
    {
        $class = self::getClassname($class);
        $reflection = new \ReflectionClass($class);

        $methods = get_class_methods($class);
        $methods = array_filter(
            $methods,
            function ($i) {
                return !in_array($i, Stub::$magicMethods);
            }
        );
        $mock = self::generateMock($class, $methods, [], '', false, $testCase);
        self::bindParameters($mock, $params);

        return self::markAsMock($mock, $reflection);
    }

    /**
     * Clones an object and redefines it's properties (even protected and private)
     *
     * @param       $obj
     * @param array $params
     *
     * @return mixed
     */
    public static function copy($obj, $params = [])
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
     * Stub::construct('User', array('autosave' => false), array('name' => 'davert'));
     * ?>
     * ```
     *
     * Accepts either name of class or object of that class
     *
     * ``` php
     * <?php
     * Stub::construct(new User, array('autosave' => false), array('name' => 'davert'));
     * ?>
     * ```
     *
     * To replace method provide it's name as a key in third parameter
     * and it's return value or callback function as parameter
     *
     * ``` php
     * <?php
     * Stub::construct('User', array(), array('save' => function () { return true; }));
     * Stub::construct('User', array(), array('save' => true }));
     * ?>
     * ```
     *
     * @param mixed $class
     * @param array $constructorParams
     * @param array $params
     * @param bool|\PHPUnit_Framework_TestCase $testCase
     *
     * @return object
     */
    public static function construct($class, $constructorParams = [], $params = [], $testCase = false)
    {
        $class = self::getClassname($class);
        $reflection = new \ReflectionClass($class);

        $callables = self::getMethodsToReplace($reflection, $params);

        $arguments = empty($callables) ? null : array_keys($callables);
        $mock = self::generateMock($class, $arguments, $constructorParams, $testCase);
        self::bindParameters($mock, $params);

        return self::markAsMock($mock, $reflection);
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
     * Stub::constructEmpty('User', array('autosave' => false), array('name' => 'davert'));
     * ?>
     * ```
     *
     * Accepts either name of class or object of that class
     *
     * ``` php
     * <?php
     * Stub::constructEmpty(new User, array('autosave' => false), array('name' => 'davert'));
     * ?>
     * ```
     *
     * To replace method provide it's name as a key in third parameter
     * and it's return value or callback function as parameter
     *
     * ``` php
     * <?php
     * Stub::constructEmpty('User', array(), array('save' => function () { return true; }));
     * Stub::constructEmpty('User', array(), array('save' => true }));
     * ?>
     * ```
     *
     * @param mixed $class
     * @param array $constructorParams
     * @param array $params
     * @param bool|\PHPUnit_Framework_TestCase $testCase
     *
     * @return object
     */
    public static function constructEmpty($class, $constructorParams = [], $params = [], $testCase = false)
    {
        $class = self::getClassname($class);
        $reflection = new \ReflectionClass($class);

        $methods = get_class_methods($class);
        $methods = array_filter(
            $methods,
            function ($i) {
                return !in_array($i, Stub::$magicMethods);
            }
        );
        $mock = self::generateMock($class, $methods, $constructorParams, $testCase);
        self::bindParameters($mock, $params);

        return self::markAsMock($mock, $reflection);
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
     * Stub::constructEmptyExcept('User', 'save', array('autosave' => false), array('name' => 'davert'));
     * ?>
     * ```
     *
     * Accepts either name of class or object of that class
     *
     * ``` php
     * <?php
     * Stub::constructEmptyExcept(new User, 'save', array('autosave' => false), array('name' => 'davert'));
     * ?>
     * ```
     *
     * To replace method provide it's name as a key in third parameter
     * and it's return value or callback function as parameter
     *
     * ``` php
     * <?php
     * Stub::constructEmptyExcept('User', 'save', array(), array('save' => function () { return true; }));
     * Stub::constructEmptyExcept('User', 'save', array(), array('save' => true }));
     * ?>
     * ```
     *
     * @param mixed $class
     * @param string $method
     * @param array $constructorParams
     * @param array $params
     * @param bool|\PHPUnit_Framework_TestCase $testCase
     *
     * @return object
     */
    public static function constructEmptyExcept(
        $class,
        $method,
        $constructorParams = [],
        $params = [],
        $testCase = false
    ) {
        $class = self::getClassname($class);
        $reflectionClass = new \ReflectionClass($class);
        $methods = $reflectionClass->getMethods();
        $methods = array_filter(
            $methods,
            function ($m) {
                return !in_array($m->name, Stub::$magicMethods);
            }
        );
        $methods = array_filter(
            $methods,
            function ($m) use ($method) {
                return $method != $m->name;
            }
        );
        $methods = array_map(
            function ($m) {
                return $m->name;
            },
            $methods
        );
        $methods = count($methods) ? $methods : null;
        $mock = self::generateMock($class, $methods, $constructorParams, $testCase);
        self::bindParameters($mock, $params);

        return self::markAsMock($mock, $reflectionClass);
    }

    private static function generateMock()
    {
        return self::doGenerateMock(func_get_args());
    }

    /**
     * Returns a mock object for the specified abstract class with all abstract
     * methods of the class mocked. Concrete methods to mock can be specified with
     * the last parameter
     *
     * @param  string $originalClassName
     * @param  array $arguments
     * @param  string $mockClassName
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @param  array $mockedMethods
     * @param  boolean $cloneArguments
     *
     * @return object
     * @since  Method available since Release 1.0.0
     * @throws \InvalidArgumentException
     */
    private static function generateMockForAbstractClass()
    {
        return self::doGenerateMock(func_get_args(), true);
    }

    private static function doGenerateMock($args, $isAbstract = false)
    {
        $testCase = self::extractTestCaseFromArgs($args);
        $methodName = $isAbstract ? 'getMockForAbstractClass' : 'getMock';
        $generatorClass = new \PHPUnit_Framework_MockObject_Generator;

        // using PHPUnit 5.4 mocks registration
        if (version_compare(\PHPUnit_Runner_Version::series(), '5.4', '>=')
            && $testCase instanceof \PHPUnit_Framework_TestCase
        ) {
            $mock = call_user_func_array([$generatorClass, $methodName], $args);
            $testCase->registerMockObject($mock);
            return $mock;
        }

        if ($testCase instanceof  \PHPUnit_Framework_TestCase) {
            $generatorClass = $testCase;
        }

        return call_user_func_array([$generatorClass, $methodName], $args);
    }

    private static function extractTestCaseFromArgs(&$args)
    {
        $argsLength = count($args) - 1;
        $testCase = $args[$argsLength];

        unset($args[$argsLength]);

        return $testCase;
    }

    /**
     * Replaces properties of current stub
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $params
     *
     * @return mixed
     * @throws \LogicException
     */
    public static function update($mock, array $params)
    {
        //do not rely on __mocked property, check typ eof $mock
        if (!$mock instanceof \PHPUnit_Framework_MockObject_MockObject) {
            throw new \LogicException('You can update only stubbed objects');
        }

        self::bindParameters($mock, $params);

        return $mock;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $params
     */
    protected static function bindParameters($mock, $params)
    {
        $reflectionClass = new \ReflectionClass($mock);
        if ($mock instanceof \PHPUnit_Framework_MockObject_MockObject) {
            $parentClass = $reflectionClass->getParentClass();
            if ($parentClass !== false) {
                $reflectionClass = $reflectionClass->getParentClass();
            }
        }

        foreach ($params as $param => $value) {
            // redefine method
            if ($reflectionClass->hasMethod($param)) {
                if ($value instanceof StubMarshaler) {
                    $marshaler = $value;
                    $mock
                        ->expects($marshaler->getMatcher())
                        ->method($param)
                        ->will(new \PHPUnit_Framework_MockObject_Stub_ReturnCallback($marshaler->getValue()));
                } elseif ($value instanceof \Closure) {
                    $mock
                        ->expects(new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)
                        ->method($param)
                        ->will(new \PHPUnit_Framework_MockObject_Stub_ReturnCallback($value));
                } elseif ($value instanceof ConsecutiveMap) {
                    $consecutiveMap = $value;
                    $mock
                        ->expects(new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)
                        ->method($param)
                        ->will(new \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($consecutiveMap->getMap()));
                } else {
                    $mock
                        ->expects(new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)
                        ->method($param)
                        ->will(new \PHPUnit_Framework_MockObject_Stub_Return($value));
                }
            } elseif ($reflectionClass->hasProperty($param)) {
                $reflectionProperty = $reflectionClass->getProperty($param);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($mock, $value);
                continue;
            } else {
                if ($reflectionClass->hasMethod('__set')) {
                    try {
                        $mock->{$param} = $value;
                    } catch (\Exception $e) {
                        throw new \PHPUnit_Framework_Exception(
                            sprintf(
                                'Could not add property %1$s, class %2$s implements __set method, '
                                . 'and no %1$s property exists',
                                $param,
                                $reflectionClass->getName()
                            ),
                            $e->getCode(),
                            $e
                        );
                    }
                } else {
                    $mock->{$param} = $value;
                }
                continue;
            }
        }
    }

    /**
     * @todo should be simplified
     */
    protected static function getClassname($object)
    {
        if (is_object($object)) {
            return get_class($object);
        }

        if (is_callable($object)) {
            return call_user_func($object);
        }

        return $object;
    }

    /**
     * @param \ReflectionClass $reflection
     * @param $params
     * @return array
     */
    protected static function getMethodsToReplace(\ReflectionClass $reflection, $params)
    {
        $callables = [];
        foreach ($params as $method => $value) {
            if ($reflection->hasMethod($method)) {
                $callables[$method] = $value;
            }
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
     *
     * @return StubMarshaler
     */
    public static function never($params = null)
    {
        return new StubMarshaler(
            new \PHPUnit_Framework_MockObject_Matcher_InvokedCount(0),
            self::closureIfNull($params)
        );
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
     * $user = Stub::make(
     *     'User',
     *     array(
     *         'getName' => Stub::once(function() { return 'Davert';}),
     *         'someMethod' => function() {}
     *     )
     * );
     * $userName = $user->getName();
     * $this->assertEquals('Davert', $userName);
     * ?>
     * ```
     *
     * @param mixed $params
     *
     * @return StubMarshaler
     */
    public static function once($params = null)
    {
        return new StubMarshaler(
            new \PHPUnit_Framework_MockObject_Matcher_InvokedCount(1),
            self::closureIfNull($params)
        );
    }

    /**
     * Checks if a method has been invoked at least one
     * time.
     *
     * If the number of invocations is 0 it will throw an exception in verify.
     *
     * ``` php
     * <?php
     * $user = Stub::make(
     *     'User',
     *     array(
     *         'getName' => Stub::atLeastOnce(function() { return 'Davert';}),
     *         'someMethod' => function() {}
     *     )
     * );
     * $user->getName();
     * $user->getName();
     * ?>
     * ```
     *
     * @param mixed $params
     *
     * @return StubMarshaler
     */
    public static function atLeastOnce($params = null)
    {
        return new StubMarshaler(
            new \PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce,
            self::closureIfNull($params)
        );
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
     * $user = Stub::make(
     *     'User',
     *     array(
     *         'getName' => Stub::exactly(3, function() { return 'Davert';}),
     *         'someMethod' => function() {}
     *     )
     * );
     * $user->getName();
     * $user->getName();
     * $user->getName();
     * ?>
     * ```
     *
     * @param int $count
     * @param mixed $params
     *
     * @return StubMarshaler
     */
    public static function exactly($count, $params = null)
    {
        return new StubMarshaler(
            new \PHPUnit_Framework_MockObject_Matcher_InvokedCount($count),
            self::closureIfNull($params)
        );
    }

    private static function closureIfNull($params)
    {
        if ($params == null) {
            return function () {
            };
        } else {
            return $params;
        }
    }

    /**
     * Stubbing a method call to return a list of values in the specified order.
     *
     * ``` php
     * <?php
     * $user = Stub::make('User', array('getName' => Stub::consecutive('david', 'emma', 'sam', 'amy')));
     * $user->getName(); //david
     * $user->getName(); //emma
     * $user->getName(); //sam
     * $user->getName(); //amy
     * ?>
     * ```
     *
     * @return ConsecutiveMap
     */
    public static function consecutive()
    {
        return new ConsecutiveMap(func_get_args());
    }
}
