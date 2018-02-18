<?php
namespace Codeception\Test\Feature;

trait AssertionCounter
{
    protected $numAssertions = 0;

    public function getNumAssertions()
    {
        return $this->numAssertions;
    }

    protected function assertionCounterStart()
    {
        \PHPUnit\Framework\Assert::resetCount();
    }

    protected function assertionCounterEnd()
    {
        $this->numAssertions = \PHPUnit\Framework\Assert::getCount();
    }
}
