<?php

class DummyClass
{
    protected $checkMe = 1;

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

}
