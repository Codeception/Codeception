<?php

namespace Attrs;

use AttrsTester;
use Codeception\Attribute\After;
use Codeception\Attribute\Before;
use Codeception\Attribute\DataProvider;
use Codeception\Attribute\Depends;
use Codeception\Attribute\Examples;
use Codeception\Attribute\Group;
use Codeception\Attribute\Incomplete;
use Codeception\Attribute\Skip;
use Codeception\Example;

class BasicScenarioCest
{
    #[Before('open1', 'open2')]
    #[After('close1')]
    #[Group('g1', 'g2')]
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

    #[Group('e1')]
    #[Examples([1, 1], [2, 2])]
    public function exampleTest(AttrsTester $I, Example $e)
    {
        $I->assertEquals($e[1], $e[0]);
    }

    #[Group('d1')]
    #[DataProvider('_listItems')]
    public function dataProviderTest(AttrsTester $I, Example $e)
    {
        $I->assertEquals($e[1], $e[0]);
    }

    #[Group('dp')]
    #[Depends('validTest')]
    public function dependsTest(AttrsTester $I)
    {
        $I->assertEquals(2, 2);
    }


    private function _listItems()
    {
        return [
            [1,1],
            [2,2],
        ];
    }

    #[Group('incomplete')]
    #[Incomplete]
    public function incompleteTest(AttrsTester $I)
    {
        $I->assertEquals(1, 2);
    }

    #[Group('skip')]
    #[Skip]
    public function skipTest(AttrsTester $I)
    {
        $I->assertEquals(1, 2);
    }
}
