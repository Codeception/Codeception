<?php
class FailedCest {


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
     * @param OrderGuy $I
     */
    public function useVariousWrappersForOrder(OrderGuy $I)
    {
        $I->appendToFile('%');
        throw new Exception('Ups');
    }

    /**
     * @param OrderGuy $I
     */
    protected function _failed(OrderGuy $I)
    {
        $I->appendToFile('F');
    }

    protected function _after(OrderGuy $I)
    {
        $I->appendToFile('S');
    }

}