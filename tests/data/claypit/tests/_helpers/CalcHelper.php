<?php
namespace Math;

use Math\AdderHelper as Adder;
use Math\SubtractorHelper as Subtractor;

class CalcHelper extends \Codeception\Module
{
    /**
     * @var Adder
     */
    protected $adder;

    /**
     * @var Subtractor
     */
    protected $subtractor;

    protected $pi;

    public function __construct(Adder $adder, Subtractor $subtractor, $pi = 3)
    {
        $this->adder = $adder;
        $this->subtractor = $subtractor;
        $this->pi = $pi;
    }

    public function add($a, $b)
    {
        return $this->adder->perform($a, $b);
    }

    public function subtract($a, $b)
    {
        return $this->subtractor->perform($a, $b);
    }

    public function squareOfCircle($radius)
    {
        return $this->pi * pow($radius, 2);
    }
}
