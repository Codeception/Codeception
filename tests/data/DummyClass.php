<?php

class DummyClass
{
    protected $checkMe = 1;
    protected $properties = array('checkMeToo' => 1);

    function __construct($checkMe = 1)
    {
        $this->checkMe = "constructed: ".$checkMe;
    }

    public function helloWorld() {
        return "hello";
    }

    public function goodByeWorld() {
        return "good bye";
    }

    protected function notYourBusinessWorld()
    {
        return "goAway";
    }

    public function getCheckMe() {
        return $this->checkMe;
    }

    public function getCheckMeToo() {
        return $this->checkMeToo;
    }

    public function call() {
        $this->targetMethod();
        return true;
    }

    public function targetMethod() {
        return true;
    }

    public function exceptionalMethod() {
        throw new Exception('Catch it!');
    }

    public function __set($name, $value) {
        if ($this->isMagical($name)) {
            $this->properties[$name] = $value;
        }
    }

    public function __get($name) {
        if ($this->__isset($name)) {
            return $this->properties[$name];
        }
    }

    public function __isset($name) {
        return $this->isMagical($name) && isset($this->properties[$name]);
    }

    private function isMagical($name) {
        $reflectionClass = new \ReflectionClass($this);
        return !$reflectionClass->hasProperty($name);
    }
}
