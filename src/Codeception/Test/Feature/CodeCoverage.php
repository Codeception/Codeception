<?php
namespace Codeception\Test\Feature;

use Codeception\Test\Descriptor;

trait CodeCoverage
{
    /**
     * @return \PHPUnit_Framework_TestResult
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

        if (method_exists($this, 'getLinesToBeCovered')) {
            $linesToBeCovered = $this->getLinesToBeCovered();
        } else {
            $linesToBeCovered = [];
        }

        if (method_exists($this, 'getLinesToBeUsed')) {
            $linesToBeUsed = $this->getLinesToBeUsed();
        } else {
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
