<?php
namespace Codeception\Test\Feature;

use Codeception\Test\Metadata;

trait IgnoreIfMetadataBlocked
{
    /**
     * @return Metadata
     */
    abstract protected function getMetadata();

    abstract protected function ignore($ignored);

    /**
     * @return \PHPUnit\Framework\TestResult
     */
    abstract protected function getTestResultObject();

    protected function ignoreIfMetadataBlockedStart()
    {
        if (!$this->getMetadata()->isBlocked()) {
            return;
        }

        $this->ignore(true);

        if ($this->getMetadata()->getSkip() !== null) {
            $this->getTestResultObject()->addFailure($this, new \PHPUnit\Framework\SkippedTestError((string)$this->getMetadata()->getSkip()), 0);
            return;
        }
        if ($this->getMetadata()->getIncomplete() !== null) {
            $this->getTestResultObject()->addFailure($this, new \PHPUnit\Framework\IncompleteTestError((string)$this->getMetadata()->getIncomplete()), 0);
            return;
        }
    }
}
