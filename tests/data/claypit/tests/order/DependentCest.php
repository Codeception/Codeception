<?php


class DependentCest {

    /**
     * @depends firstOne
     */
    public function secondOne(OrderGuy $I)
    {

    }

    public function firstOne(OrderGuy $I)
    {
        $I->failNow();
    }
}