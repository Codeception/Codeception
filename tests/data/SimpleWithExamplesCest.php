<?php

use Codeception\Attribute\Examples;

class SimpleWithExamplesCest
{
    #[Examples('foo', 'bar')]
    #[Examples(1, 2)]
    #[Examples(true, false)]
    public function helloWorld(\CodeGuy $I, \Codeception\Example $example)
    {
        $I->execute(function ($example) {
            if (!is_array($example)) {
                return false;
            }

            return count($example);
        })->seeResultEquals(2);
    }
}
