<?php

use Codeception\Attribute\Depends;

class Dependent2Cest
{
    #[Depends(DependentCest::class . ':firstOne')]
    public function thirdOne(OrderGuy $I)
    {
    }
}
