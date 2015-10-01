<?php


class Dependent2Cest {

    /**
     * @depends DependentCest:firstOne
     */
    public function thirdOne(OrderGuy $I)
    {
    }

}