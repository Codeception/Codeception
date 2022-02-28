<?php

namespace Math;

class CalcHelper extends \Codeception\Module
{
    protected Adder $adder;

    protected Subtractor $subtractor;

    protected Divider $divider;

    protected int $pi = 3;


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
        return $this->pi * $radius ** 2;
    }
}
