<?php

class ObjectArgumentCest
{
    public function passObject(UnitTester $I): void
    {
        $I->assertNotNull(new ArrayObject());
    }

    public function failWithObject(UnitTester $I): void
    {
        $I->assertNull(new ArrayObject());
    }

    public function failWithStringable(UnitTester $I): void
    {
        $I->assertNull(new SplFileInfo('stringable.txt'));
    }
}
