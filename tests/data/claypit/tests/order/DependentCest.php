<?php


class DependentCest {

    public function firstOne(OrderGuy $I)
    {
        $I->failNow();
    }

    /**
     * @depends firstOne
     */
    public function secondOne(OrderGuy $I)
    {

    }

}