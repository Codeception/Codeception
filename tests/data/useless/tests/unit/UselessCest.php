<?php

class UselessCest
{
    public function makeNoAssertions(UnitTester $I)
    {
        $I->comment('make no assertions');
    }
}
