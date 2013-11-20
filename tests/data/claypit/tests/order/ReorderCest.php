<?php
class ReorderCest {

    /**
     * @before a0
     * @after a2
     */
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

    /**
     * @before a1
     * @after a5
     * @param OrderGuy $I
     */
    public function useVariousWrappersForOrder(OrderGuy $I)
    {
        $I->appendToFile('3');
    }

    /**
     * @before a4
     * @after a6
     * @param OrderGuy $I
     */
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