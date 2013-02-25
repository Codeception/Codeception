<?php
namespace Codeception\Util;

class Stub
{
    public static function make($class, $params = array())
    {
        $class = self::getClassname($class);
        if (!class_exists($class)) throw new \RuntimeException("Stubbed class $class doesn't exist.");
        $reflection = new \ReflectionClass($class);

        $callables = self::getMethodsToReplace($reflection, $params);
        if (!empty($callables)) {
            if ($reflection->isAbstract()) {
                $mock = \PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass($class, array_keys($callables), '', false);
            } else {
                $mock = \PHPUnit_Framework_MockObject_Generator::getMock($class, array_keys($callables), array(), '', false);
            }
        } else {
            if ($reflection->isAbstract()) {
                $mock = \PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass($class, array(), '', false);
            } else {
                $mock = \PHPUnit_Framework_MockObject_Generator::getMock($class, null, array(), '', false);
            }
        }
        self::bindParameters($mock, $params);
        $mock->__mocked = $class;
        return $mock;
    }

    public static function factory($class, $num = 1, $params = array())
    {
        $objs = array();
        for ($i = 0; $i < $num; $i++) $objs[] = self::make($class, $params);
        return $objs;
    }

    public static function makeEmptyExcept($class, $method, $params = array())
    {
        $class = self::getClassname($class);
        $reflectionClass = new \ReflectionClass($class);
        $methods = $reflectionClass->getMethods();
        $methods = array_filter($methods, function ($m) use ($method) {
            return $method != $m->name;
        });
        $methods = array_map(function ($m) {
            return $m->name;
        }, $methods);
        $mock = \PHPUnit_Framework_MockObject_Generator::getMock($class, $methods, array(), '', false);
        self::bindParameters($mock, $params);
        $mock->__mocked = $class;
        return $mock;
    }

    public static function makeEmpty($class, $params = array())
    {
        $class = self::getClassname($class);
        $mock = \PHPUnit_Framework_MockObject_Generator::getMock($class, array(), array(), '', false);
        self::bindParameters($mock, $params);
        $mock->__mocked = $class;
        return $mock;
    }

    public static function copy($obj, $params = array())
    {
        $copy = clone($obj);
        self::bindParameters($copy, $params);
        return $copy;
    }

    public static function construct($class, $constructorParams = array(), $params = array())
    {
        $class = self::getClassname($class);
        $callables = self::getMethodsToReplace(new \ReflectionClass($class), $params);

        if (!empty($callables)) {
            $mock = \PHPUnit_Framework_MockObject_Generator::getMock($class, array_keys($callables), $constructorParams);
        } else {
            $mock = \PHPUnit_Framework_MockObject_Generator::getMock($class, null, $constructorParams);
        }
        self::bindParameters($mock, $params);
        $mock->__mocked = $class;
        return $mock;

    }

    public static function constructEmpty($class, $constructorParams = array(), $params = array())
    {
        $class = self::getClassname($class);
        $mock = \PHPUnit_Framework_MockObject_Generator::getMock($class, array(), $constructorParams);
        self::bindParameters($mock, $params);
        $mock->__mocked = $class;
        return $mock;
    }

    public static function constructEmptyExcept($class, $method, $constructorParams = array(), $params = array())
    {
        $class = self::getClassname($class);
        $reflectionClass = new \ReflectionClass($class);
        $methods = $reflectionClass->getMethods();
        $methods = array_filter($methods, function ($m) use ($method) {
            return $method != $m->name;
        });
        $methods = array_map(function ($m) {
            return $m->name;
        }, $methods);
        $mock = \PHPUnit_Framework_MockObject_Generator::getMock($class, $methods, $constructorParams);
        self::bindParameters($mock, $params);
        $mock->__mocked = $class;
        return $mock;
    }

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
                if ($value instanceof \Closure) {
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

}
