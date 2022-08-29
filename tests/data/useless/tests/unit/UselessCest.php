<?php

class UselessCest
{
    public function makeNoAssertions(UnitTester $I): void
    {
        $I->comment('make no assertions');
    }
}
