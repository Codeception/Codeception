<?php

use Codeception\Attribute\After;
use Codeception\Attribute\Before;

class ReorderCest
{
    #[Before('a0')]
    #[After('a2')]
    protected function a1(OrderGuy $I)
    {
        $I->appendToFile('1');
    }

    protected function a0(OrderGuy $I)
    {
        $I->appendToFile('0');
    }

    protected function a2(OrderGuy $I)
    {
        $I->appendToFile('2');
    }

    #[Before('a1')]
    #[After('a5')]
    public function useVariousWrappersForOrder(OrderGuy $I)
    {
        $I->appendToFile('3');
    }

    #[Before('a4')]
    #[After('a6')]
    protected function a5(OrderGuy $I)
    {
        $I->appendToFile('5');
    }

    protected function a4(OrderGuy $I)
    {
        $I->appendToFile('4');
    }

    protected function a6(OrderGuy $I)
    {
        $I->appendToFile('6');
    }
}
