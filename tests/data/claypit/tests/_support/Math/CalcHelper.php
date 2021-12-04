<?php
namespace Math;

class CalcHelper extends \Codeception\Module
{
    /**
     * @var Adder
     */
    protected Adder $adder;

    /**
     * @var Subtractor
     */
    protected Subtractor $subtractor;

    protected int $pi = 3;

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
        return $this->pi * $radius ** 2;
    }
}
