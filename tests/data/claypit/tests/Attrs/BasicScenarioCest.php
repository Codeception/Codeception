<?php

namespace Attrs;

use \AttrsTester;
use Codeception\Example;

class BasicScenarioCest
{
    #[\before('open1', 'open2')]
    #[\after('close1')]
    #[\group('g1', 'g2')]
    public function validTest(AttrsTester $I)
    {
        $I->assertEquals(1, 1);
    }

    private function open1(AttrsTester $I)
    {
        $I->comment('open1');
    }

    private function open2(AttrsTester $I)
    {
        $I->comment('open2');
    }

    private function close1(AttrsTester $I)
    {
        $I->comment('close1');
    }

    #[\group('e1')]
    #[\example([1, 1], [2, 2])]
    public function exampleTest(AttrsTester $I, Example $e)
    {
        $I->assertEquals($e[1], $e[0]);
    }

    #[\group('d1')]
    #[\dataProvider('_listItems')]
    public function dataProviderTest(AttrsTester $I, Example $e)
    {
        $I->assertEquals($e[1], $e[0]);
    }

    #[\group('dp')]
    #[\depends('validTest')]
    public function dependsTest(AttrsTester $I)
    {
        $I->assertEquals(2, 2);
    }


    private function _listItems() {
        return [
            [1,1],
            [2,2],
        ];
    }

    #[\group('incomplete')]
    #[\incomplete]
    public function incompleteTest(AttrsTester $I)
    {
        $I->assertEquals(1, 2);
    }

    #[\group('skip')]
    #[\skip]
    public function skipTest(AttrsTester $I)
    {
        $I->assertEquals(1, 2);
    }

}
