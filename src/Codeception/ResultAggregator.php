<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Event\FailEvent;
use Codeception\Test\Test;

class ResultAggregator
{
    /**
     * @var bool Stop execution of test suite if this property is true
     */
    private bool $stop = false;

    /**
     * @var FailEvent[]
     */
    private array $failures = [];

    /**
     * @var FailEvent[]
     */
    private array $errors = [];

    /**
     * @var FailEvent[]
     */
    private array $warnings = [];

    /**
     * @var FailEvent[]
     */
    private array $useless = [];

    /**
     * @var FailEvent[]
     */
    private array $skipped = [];

    /**
     * @var FailEvent[]
     */
    private array $incomplete = [];
    private int $count = 0;
    private int $successful = 0;
    private int $assertions = 0;

    public function stop(): void
    {
        $this->stop = true;
    }

    public function shouldStop(): bool
    {
        return $this->stop;
    }

    public function addTest(Test $test): void
    {
        ++$this->count;
    }

    public function addSuccessful(Test $test): void
    {
        ++$this->successful;
    }

    public function addFailure(FailEvent $e): void
    {
        $this->failures[] = $e;
    }

    public function addError(FailEvent $e): void
    {
        $this->errors[] = $e;
    }

    public function addWarning(FailEvent $e): void
    {
        $this->warnings[] = $e;
    }

    public function addSkipped(FailEvent $e): void
    {
        $this->skipped[] = $e;
    }

    public function addIncomplete(FailEvent $e): void
    {
        $this->incomplete[] = $e;
    }

    public function addUseless(FailEvent $e): void
    {
        $this->useless[] = $e;
    }

    public function addToAssertionCount(int $n): void
    {
        $this->assertions += $n;
    }

    /**
     * @return FailEvent[]
     */
    public function failures(): array
    {
        return $this->failures;
    }

    /**
     * @return FailEvent[]
     */
    public function errors(): array
    {
        return $this->errors;
    }


    /**
     * @return FailEvent[]
     */
    public function useless(): array
    {
        return $this->useless;
    }

    /**
     * @return FailEvent[]
     */

    public function incomplete(): array
    {
        return $this->incomplete;
    }

    /**
     * @return FailEvent[]
     */
    public function skipped(): array
    {
        return $this->skipped;
    }

    public function wasSuccessful(): bool
    {
        return $this->errorCount() + $this->failureCount() + $this->warningCount() === 0;
    }

    public function wasSuccessfulIgnoringWarnings(): bool
    {
        return $this->errorCount() + $this->failureCount() === 0;
    }

    /**
     * @deprecated replaced by wasSuccessfulAndNoTestIsUselessOrSkippedOrIncomplete
     */
    public function wasSuccessfulAndNoTestIsRiskyOrSkippedOrIncomplete(): bool
    {
        return $this->wasSuccessfulAndNoTestIsUselessOrSkippedOrIncomplete();
    }

    public function wasSuccessfulAndNoTestIsUselessOrSkippedOrIncomplete(): bool
    {
        return $this->wasSuccessful()
            && $this->uselessCount() + $this->skippedCount() + $this->incompleteCount() === 0;
    }

    public function testCount(): int
    {
        return $this->count;
    }

    public function successfulCount(): int
    {
        return $this->successful;
    }

    public function assertionCount(): int
    {
        return $this->assertions;
    }

    public function skippedCount(): int
    {
        return count($this->skipped);
    }

    public function incompleteCount(): int
    {
        return count($this->incomplete);
    }

    public function errorCount(): int
    {
        return count($this->errors);
    }

    public function failureCount(): int
    {
        return count($this->failures);
    }

    public function warningCount(): int
    {
        return count($this->warnings);
    }

    public function uselessCount(): int
    {
        return count($this->useless);
    }

    public function popLastFailure(): ?FailEvent
    {
        return array_pop($this->failures);
    }

    public function getLastFailure(): ?FailEvent
    {
        return end($this->failures) ?: null;
    }
}
