<?php

class Stub
{
    public static function make($class, $params = array())
    {
        if (!class_exists($class)) throw new \RuntimeException("Stubbed class $class doesn't exist. Use Stub::factory instead");
        $reflection = new \ReflectionClass($class);
        $methods = $reflection->getMethods();
        if (count($methods)) {
            foreach ($methods as $method) {
                if ($method->isConstructor() or ($method->isDestructor())) continue;
                $method = $method->name;
                if ($reflection->isAbstract()) {
                    $mock = PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass($class, array($method), '', false);
                } else {
                    $mock = PHPUnit_Framework_MockObject_Generator::getMock($class, array($method), array(), '', false);
                }
                $mock->expects(new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)
                        ->method($method)
                        ->will(new PHPUnit_Framework_MockObject_Stub_Return(null));
                self::bindParameters($mock, $params);
                $mock->__mocked = $class;
                return $mock;
            }
        }
        return self::makeEmpty($class, $params);
    }

    public static function makeEmpty($class, $params = array())
    {
        if (!class_exists($class)) throw new \RuntimeException("Stubbed class $class doesn't exist. Use Stub::factory instead");
        return self::factory($class, $params);
    }

    public static function factory($class, $params = array(), $parent = '')
    {
        $mock = PHPUnit_Framework_MockObject_Generator::getMock($class, array(), array(), '', false);
        self::bindParameters($mock, $params);
        $mock->__mocked = $class;
        return $mock;

    }

    protected static function bindParameters($mock, $params)
    {
        $reflectionClass = new ReflectionClass($mock);
        foreach ($params as $param => $value) {
            if (!is_callable($value)) {

                $reflectionProperty = $reflectionClass->getProperty($param);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($mock, $value);
                continue;
            }
            $mock->
                    expects(new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)->
                    method($param)->
                    will(new PHPUnit_Framework_MockObject_Stub_ReturnCallback($value));
        }

    }

}
