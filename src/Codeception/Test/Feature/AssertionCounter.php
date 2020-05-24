<?php
namespace Codeception\Test\Feature;

trait AssertionCounter
{
    protected $numAssertions = 0;

    public function getNumAssertions()
    {
        return $this->numAssertions;
    }
    /**
     * This method is not covered by the backward compatibility promise
     * for PHPUnit, but is nice to have for extensions.
     */
    public function addToAssertionCount($count)
    {
        $this->numAssertions += $count;
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
