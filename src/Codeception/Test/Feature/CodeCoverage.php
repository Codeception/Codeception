<?php

declare(strict_types=1);

namespace Codeception\Test\Feature;

use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\StrictCoverage;
use Codeception\Test\Test as CodeceptTest;
use PHP_CodeCoverage_Exception;
use PHPUnit\Framework\TestResult;

trait CodeCoverage
{
    abstract public function getTestResultObject(): TestResult;

    public function codeCoverageStart(): void
    {
        $codeCoverage = $this->getTestResultObject()->getCodeCoverage();
        if (!$codeCoverage) {
            return;
        }
        $codeCoverage->start(Descriptor::getTestSignature($this));
    }

    public function codeCoverageEnd(string $status, float $time): void
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
        } catch (PHP_CodeCoverage_Exception $exception) {
            if ($status === CodeceptTest::STATUS_OK) {
                $this->getTestResultObject()->addError($this, $exception, $time);
            }
        }
    }
}
