<?php

declare(strict_types=1);

namespace Codeception\Test\Feature;

use Codeception\Test\Metadata;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\SkippedWithMessageException;
use PHPUnit\Framework\TestResult;
use PHPUnit\Runner\Version as PHPUnitVersion;

trait IgnoreIfMetadataBlocked
{
    abstract public function getMetadata(): Metadata;

    abstract protected function ignore(bool $ignored): void;

    abstract protected function getTestResultObject(): TestResult;

    protected function ignoreIfMetadataBlockedStart(): void
    {
        if (!$this->getMetadata()->isBlocked()) {
            return;
        }

        $this->ignore(true);

        if ($this->getMetadata()->getSkip() !== null) {
            $skipMessage = (string)$this->getMetadata()->getSkip();
            if (PHPUnitVersion::series() < 10) {
                $skippedTestError = new SkippedTestError($skipMessage);
            } else {
                $skippedTestError = new SkippedWithMessageException($skipMessage);
            }

            $this->getTestResultObject()->addFailure($this, $skippedTestError, 0);
            return;
        }

        if ($this->getMetadata()->getIncomplete() !== null) {
            $incompleteTestError = new IncompleteTestError((string)$this->getMetadata()->getIncomplete());
            $this->getTestResultObject()->addFailure($this, $incompleteTestError, 0);
        }
    }
}
