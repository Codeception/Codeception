<?php

use Codeception\Attribute\DataProvider;
use Codeception\Attribute\Examples;
use Codeception\Example;

class SimpleWithDataProviderArrayCest
{
    #[DataProvider('getTestData')]
    public function helloWorld(CodeGuy $I, Example $example)
    {
        $I->execute(function ($example) {
            if (!is_array($example)) {
                return false;
            }

            return count($example);
        })->seeResultEquals(2);
    }

    protected function getTestData(): array
    {
        return [
            ['foo', 'bar'],
            [1, 2],
            [true, false],
        ];
    }
}
