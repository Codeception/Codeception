<?php

class TwoTestsCest
{
    public function failing(UnitTester $I)
    {
        throw new \RuntimeException('error');
    }

    public function successful(UnitTester $I)
    {
        $I->assertTrue(true);
    }
}
