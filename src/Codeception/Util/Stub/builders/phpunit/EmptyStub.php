<?php

class EmptyStub extends AbstractStub
{
    function __construct($class, $params = array())
    {
        if (!class_exists($class)) throw new \RuntimeException("Stubbed class $class doesn't exist");
        $this->mockedClass = $class;
        $this->mock = PHPUnit_Framework_MockObject_Generator::getMock($class, array(), array(), '', false);
        $this->bindParameters($params);

    }

}
