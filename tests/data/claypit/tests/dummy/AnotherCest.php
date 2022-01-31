<?php

class AnotherCest
{

    /**
     * @group ok
     */
    public function optimistic(DumbGuy $I) {
        $I->expect('everything is ok');
    }

    public function pessimistic(DumbGuy $I)
    {
        $I->expect('everything is bad');
    }

}