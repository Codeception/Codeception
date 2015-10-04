<?php
namespace Codeception\Lib\TestFeature;

trait AssertionCounter
{
    protected $numAssertions = 0;

    public function getNumAssertions()
    {
        return $this->numAssertions;
    }

    protected function assertionCounterStart()
    {
        \PHPUnit_Framework_Assert::resetCount();
    }

    protected function assertionCounterEnd()
    {
        $this->numAssertions = \PHPUnit_Framework_Assert::getCount();
    }
}