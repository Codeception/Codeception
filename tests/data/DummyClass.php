<?php

class DummyClass
{
    
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

    protected $checkMe = 1;
    
    public function getCheckMe() {
        return $this->checkMe;
    }

}
