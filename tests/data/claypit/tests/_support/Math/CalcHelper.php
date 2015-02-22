<?php
namespace Math;

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

    public function __construct($pi = 3)
    {
        $this->pi = $pi;
    }

    protected function _inject(Adder $adder, Subtractor $subtractor)
    {
        $this->adder = $adder;
        $this->subtractor = $subtractor;
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
