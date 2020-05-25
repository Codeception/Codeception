<?php

class CoverageCest
{
    public function testAddition(MathTester $I)
    {
        $I->assertSame(1, 1);
    }
}
