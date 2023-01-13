<?php

namespace Unit;

use Codeception\Example;
use UnitTester;

class WantToCest
{
    public function iWantTo(UnitTester $I)
    {
        $I->wantTo('check if I->wantTo works');
    }

    public function testerWantTo(UnitTester $tester)
    {
        $tester->wantTo('check if tester->wantTo works');
    }

    /**
     * @dataProvider provider
     */
    public function dataProviderIWantTo(UnitTester $I, Example $example)
    {
        $I->wantTo('check if I->wantTo doesn\'t override data provider data');
    }

    /**
     * @dataProvider provider
     */
    public function dataProviderTesterWantTo(UnitTester $tester, Example $example)
    {
        $tester->wantTo('check if tester->wantTo doesn\'t override data provider data');
    }

    protected function provider(): array
    {
        return [
            ['aaa'],
            ['bbb'],
        ];
    }

    public function variableArgumentOfWantTo(UnitTester $I)
    {
        $var = 'check if variable wantTo is evaluated';
        $I->wantTo($var);
    }
}
