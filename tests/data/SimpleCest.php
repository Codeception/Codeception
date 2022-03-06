<?php

class SimpleCest
{
    public string $class = \DummyClass::class;

    public function helloWorld(\CodeGuy $I)
    {
        $I->execute(fn (): int => 2 + 2)
            ->seeResultEquals('4');
    }

    public function goodByeWorld(\CodeGuy $I)
    {
        $I->execute(fn (): int => 2 + 2)
            ->seeResultNotEquals('3');
    }
}
