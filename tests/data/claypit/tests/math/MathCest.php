<?php

use \Math\CalcHelper as Calc;

class MathCest
{
    /**
     * @var Calc
     */
    protected $calc;

    protected function _inject(Calc $calc)
    {
        $this->calc = $calc;
    }

    public function testAddition(MathTester $I)
    {
        $I->assertSame(3, $this->calc->add(1, 2));
        $I->assertSame(0, $this->calc->add(10, -10));
    }

    public function testSubtraction(MathTester $I)
    {
        $I->assertSame(1, $this->calc->subtract(3, 2));
        $I->assertSame(0, $this->calc->subtract(5, 5));
    }

    public function testSquare(MathTester $I)
    {
        $I->assertSame(3, $this->calc->squareOfCircle(1));
        $I->assertSame(12, $this->calc->squareOfCircle(2));
    }

    public function testDivision(MathTester $I)
    {
        $I->assertSame(5, $this->calc->divide(10, 2));
    }

    public function testTrigonometry(MathTester $I, \Page\Math\Trigonometry $t)
    {
        $I->assertLessThan(0.9, $t->tan(0.5));
    }

    public function testTrigonometryPage(\Page\Math\Trigonometry $t)
    {
        $t->assertTanIsLessThen(0.5, 0.9);
    }
}
