<?php

use \Math\CalcHelper as Calc;

class MathTest extends \Codeception\TestCase\Test
{
   /**
    * @var \MathTester
    */
    protected $tester;

    /**
     * @var Calc
     */
    protected $calc;

    protected function _inject(Calc $calc)
    {
        $this->calc = $calc;
    }

    public function testAll()
    {
        $this->assertEquals(3, $this->calc->add(1, 2));
        $this->assertEquals(1, $this->calc->subtract(3, 2));
        $this->assertEquals(75, $this->calc->squareOfCircle(5));
    }
}
