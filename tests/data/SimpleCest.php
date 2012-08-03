<?php

class SimpleCest
{

    public $class = 'DummyClass';

    public function helloWorld(\CodeGuy $I) {
        $I->executeTestedMethodOn(new $this->class)
            ->seeResultEquals('hello world');
    }

    public function goodByeWorld(\CodeGuy $I) {
        $I->executeTestedMethodOn(new $this->class)
            ->seeResultNotEquals('hello world');
    }

}
