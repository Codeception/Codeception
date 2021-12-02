<?php

use Math\CalcHelper as Calc;

class MathTest extends \Codeception\Test\Unit
{
   /**
    * @var \MathTester
    */
    protected MathTester $tester;

    /**
     * @var Calc
     */
    protected Calc $calc;

    protected function _inject(Calc $calc)
    {
        $this->calc = $calc;
    }

    public function testAll()
    {
        $this->assertSame(3, $this->calc->add(1, 2));
        $this->assertSame(1, $this->calc->subtract(3, 2));
        $this->assertSame(75, $this->calc->squareOfCircle(5));
    }
}
