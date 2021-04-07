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

    /**
     * @var Divider
     */
    protected $divider;

    protected $pi = 3;

    protected function _inject(Adder $adder, Subtractor $subtractor, Divider $divider)
    {
        $this->adder = $adder;
        $this->subtractor = $subtractor;
        $this->divider = $divider;
    }

    public function add($a, $b)
    {
        return $this->adder->perform($a, $b);
    }

    public function subtract($a, $b)
    {
        return $this->subtractor->perform($a, $b);
    }

    public function divide($a, $b)
    {
        return $this->divider->perfom($a, $b);
    }

    public function squareOfCircle($radius)
    {
        return $this->pi * pow($radius, 2);
    }
}
