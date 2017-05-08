<?php


class failingCest
{
    // tests
    public function failing(UnitTester $I)
    {
        throw new \RuntimeException('in cest');
    }
}
