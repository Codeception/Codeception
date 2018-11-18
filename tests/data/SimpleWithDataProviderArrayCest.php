<?php

class SimpleWithDataProviderArrayCest
{
    /**
     * @dataProvider getTestData
     */
    public function helloWorld(\CodeGuy $I, \Codeception\Example $example) {
        $I->execute(function($example) {
            if (!is_array($example)) {
                return false;
            }

            return count($example);
        })->seeResultEquals(2);
    }

    protected function getTestData()
    {
        return [
            ['foo', 'bar'],
            [1, 2],
            [true, false],
        ];
    }
}
