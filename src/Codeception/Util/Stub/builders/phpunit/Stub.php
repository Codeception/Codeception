<?php
class Stub
{
    public static function make($class, $params = array())
    {
        if (!class_exists($class)) throw new \RuntimeException("Stubbed class $class doesn't exist. Use Stub::factory instead");
        $reflection = new \ReflectionClass($class);

        $callables = array_filter($params, function ($a) { return is_callable($a); });

        if (!empty($callables)) {
            if ($reflection->isAbstract()) {
                $mock = \PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass($class, array_keys($callables), '', false);
            } else {
                $mock = \PHPUnit_Framework_MockObject_Generator::getMock($class, array_keys($callables), array(), '', false);
            }
        } else {
            if ($reflection->isAbstract()) {
                $mock = \PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass($class, null, '', false);
            } else {
                $mock = \PHPUnit_Framework_MockObject_Generator::getMock($class, null, array(), '', false);
            }
        }
        self::bindParameters($mock, $params);
        $mock->__mocked = $class;
        return $mock;
    }

    public static function makeEmptyExcept($class, $method, $params = array()) {
        $reflectionClass = new \ReflectionClass($mock);
        $methods = $reflectionClass->getMethods();
        $methods = array_filter($methods, function ($m) use ($method) { return $method != $m->name; });
        $methods = array_map(function ($m) { return $m->name; }, $methods);
        $mock = \PHPUnit_Framework_MockObject_Generator::getMock($class, $methods, array(), '', false);
        self::bindParameters($mock, $params);
        $mock->__mocked = $class;
        return $mock;
    }

    public static function makeEmpty($class, $params = array())
    {
        return self::factory($class, $params);
    }

    public static function factory($class, $params = array(), $parent = '')
    {
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

    protected static function bindParameters($mock, $params)
    {
        $reflectionClass = new \ReflectionClass($mock);

        foreach ($params as $param => $value) {
            if (!($value instanceof \Closure)) {
                $reflectionProperty = $reflectionClass->getProperty($param);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($mock, $value);
                continue;
            }
            $mock->
                    expects(new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)->
                    method($param)->
                    will(new \PHPUnit_Framework_MockObject_Stub_ReturnCallback($value));
        }

    }

}
