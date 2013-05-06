<?php

class SimpleCest
{

    public $class = 'DummyClass';

    public function helloWorld(\CodeGuy $I) {
        $I->execute(function() { return 2+2; })
            ->seeResultEquals('4');
    }

    public function goodByeWorld(\CodeGuy $I) {
        $I->execute(function() { return 2+2; })
            ->seeResultNotEquals('3');
    }

}
