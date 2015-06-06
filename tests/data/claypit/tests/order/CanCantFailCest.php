<?php

use Step\Order\CanCantSteps;

class CanCantFailCest
{
    public function testOne(CanCantSteps $I)
    {
        $I->appendToFile('T');
        $I->canSeeFailNow();
        $I->appendToFile('T');
    }

    public function testTwo(CanCantSteps $I)
    {
        $I->appendToFile('T');
        $I->canSeeFailNow();
        $I->appendToFile('T');
    }
}