<?php

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

    /**
     * @before a
     * @after b
     */
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
