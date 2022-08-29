<?php

use Codeception\Attribute\Depends;

class DependentCest
{
    #[Depends('firstOne')]
    public function secondOne(OrderGuy $I)
    {
    }

    public function firstOne(OrderGuy $I)
    {
        $I->failNow();
    }
}
