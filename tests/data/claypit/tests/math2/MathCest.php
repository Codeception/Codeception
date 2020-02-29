<?php

use \Math\CalcHelper as Calc;

class MathCest
{
    public function testAddition(MathTester $I)
    {
        $addition = new \Math2\Adder();

        $I->assertEquals(3, $addition->perform(1, 2));
        $I->assertEquals(0, $addition->perform(10, -10));
    }

    public function testSubtraction(MathTester $I)
    {
        $subtract = new \Math2\Subtractor();

        $I->assertEquals(1, $subtract->perform(3, 2));
        $I->assertEquals(0, $subtract->perform(5, 5));
    }
}
