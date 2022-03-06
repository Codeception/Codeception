<?php

class FailingCest
{
    public function failing(UnitTester $I)
    {
        throw new \RuntimeException('in cest');
    }
}
