<?php
abstract class AbstractStub implements PHPUnit_Framework_MockObject_MockObject, \Codeception\Util\Stub\Stub
{
    protected $mockedClass;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $mock;

    public function bindParameters($params) {
        foreach ($params as $param => $value) {
            if (!is_callable($value)) {
                $this->mock->$param = $value;
                continue;
            }
            $this->mock->
                    expects(new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)->
                    method($param)->
                    will(new PHPUnit_Framework_MockObject_Stub_ReturnCallback($value));
        }
    }

    public function __mocked()
    {
        return $this->mockedClass;
    }

    public function __set($key, $value) {
        $this->mock->$key = $value;
    }

    public function __isset($key) {
        return isset($this->mock->$key);
    }

    public function __unset($key) {
        unset($this->mock->$key);
    }

    public function __call($method, $args) {
        return call_user_func_array(array($this->mock, $method), $args);
    }

    public function expects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher) {
        return $this->mock->expects($matcher);
    }

    public static function staticExpects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher) {
        throw new Exception('Not implemented');
    }

    public function __phpunit_getInvocationMocker()
    {
        $this->mock->__phpunit_getInvocationMocker();
    }

    public static function __phpunit_getStaticInvocationMocker() {
        throw new Exception('Not implemented');
    }

    public function __phpunit_verify() {
        $this->mock->__phpunit_verify();
    }
}
