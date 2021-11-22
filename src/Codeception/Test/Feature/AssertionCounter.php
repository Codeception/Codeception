<?php

declare(strict_types=1);

namespace Codeception\Test\Feature;

use PHPUnit\Framework\Assert;

trait AssertionCounter
{
    protected int $numAssertions = 0;

    public function getNumAssertions(): int
    {
        return $this->numAssertions;
    }

    /**
     * This method is not covered by the backward compatibility promise
     * for PHPUnit, but is nice to have for extensions.
     */
    public function addToAssertionCount(int $count): void
    {
        $this->numAssertions += $count;
    }

    protected function assertionCounterStart(): void
    {
        Assert::resetCount();
    }

    protected function assertionCounterEnd(): void
    {
        $this->numAssertions = Assert::getCount();
    }
}
