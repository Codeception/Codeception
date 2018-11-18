<?php

class SimpleWithDataProviderYieldGeneratorCest
{
    /**
     * @dataProvider getTestData
     *
     * @example ["fizz", "buzz"]
     * @example [null, "test"]
     */
    public function helloWorld(\CodeGuy $I, \Codeception\Example $example) {
        $I->execute(function($example) {
            if (!is_array($example)) {
                return false;
            }

            return count($example);
        })->seeResultEquals(2);
    }

    /**
     * @return Generator
     */
    protected function getTestData()
    {
        yield ['foo', 'bar'];
        yield [1, 2];
        yield [true, false];
    }
}
