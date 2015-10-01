<?php
namespace Codeception\TestFormat\Decorator;

trait AssertionCounter
{
    protected $numAssertions = 0;

    public function getNumAssertions()
    {
        return $this->numAssertions;
    }

    function assertionCounterStart()
    {
        \PHPUnit_Framework_Assert::resetCount();
    }

    function assertionCounterEnd()
    {
        $this->numAssertions = \PHPUnit_Framework_Assert::getCount();
    }
}