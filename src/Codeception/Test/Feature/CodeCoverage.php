<?php
namespace Codeception\Test\Feature;

use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\StrictCoverage;

trait CodeCoverage
{
    /**
     * @return \PHPUnit\Framework\TestResult
     */
    abstract public function getTestResultObject();

    public function codeCoverageStart()
    {
        $codeCoverage = $this->getTestResultObject()->getCodeCoverage();
        if (!$codeCoverage) {
            return;
        }
        $codeCoverage->start(Descriptor::getTestSignature($this));
    }

    public function codeCoverageEnd($status, $time)
    {
        $codeCoverage = $this->getTestResultObject()->getCodeCoverage();
        if (!$codeCoverage) {
            return;
        }

        if ($this instanceof StrictCoverage) {
            $linesToBeCovered = $this->getLinesToBeCovered();
            $linesToBeUsed = $this->getLinesToBeUsed();
        } else {
            $linesToBeCovered = [];
            $linesToBeUsed = [];
        }

        try {
            $codeCoverage->stop(true, $linesToBeCovered, $linesToBeUsed);
        } catch (\PHP_CodeCoverage_Exception $cce) {
            if ($status === \Codeception\Test\Test::STATUS_OK) {
                $this->getTestResultObject()->addError($this, $cce, $time);
            }
        }
    }
}
