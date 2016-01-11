<?php
namespace Codeception\Test\Feature;

use Codeception\Test\Metadata;

trait BlockByMetadata
{
    /**
     * @return Metadata
     */
    abstract protected function getMetadata();

    abstract protected function block($blocked);

    /**
     * @return \PHPUnit_Framework_TestResult
     */
    abstract protected function getTestResultObject();

    protected function blockByMetadataStart()
    {
        if (!$this->getMetadata()->isBlocked()) {
            return;
        }

        $this->block(true);

        if ($this->getMetadata()->getSkip() !== null) {
            $this->getTestResultObject()->addFailure($this, new \PHPUnit_Framework_SkippedTestError((string)$this->getMetadata()->getSkip()), 0);
            return;
        }
        if ($this->getMetadata()->getIncomplete() !== null) {
            $this->getTestResultObject()->addFailure($this, new \PHPUnit_Framework_IncompleteTestError((string)$this->getMetadata()->getIncomplete()), 0);
            return;
        }
    }
}