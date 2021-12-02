<?php

class SimpleCest
{

    public string $class = 'DummyClass';

    public function helloWorld(\CodeGuy $I) {
        $I->execute(fn() => 2+2)
            ->seeResultEquals('4');
    }

    public function goodByeWorld(\CodeGuy $I) {
        $I->execute(fn() => 2+2)
            ->seeResultNotEquals('3');
    }

}
