<?php

use Codeception\Attribute\After;
use Codeception\Attribute\Before;

class FailedCest
{
    protected function a($I)
    {
        $I->appendToFile('a');
    }

    protected function b($I)
    {
        $I->appendToFile('b');
    }

    #[Before('a')]
    #[After('b')]
    public function useVariousWrappersForOrder(OrderGuy $I)
    {
        $I->appendToFile('%');
        throw new Exception('Ups');
    }

    protected function _failed(OrderGuy $I)
    {
        $I->appendToFile('F');
    }

    protected function _after(OrderGuy $I)
    {
        $I->appendToFile('S');
    }
}
