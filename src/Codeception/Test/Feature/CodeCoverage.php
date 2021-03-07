<?php

declare(strict_types=1);

namespace Codeception\Test\Feature;

use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\StrictCoverage;
use Codeception\Test\Test as CodeceptTest;
use SebastianBergmann\CodeCoverage\Exception as CodeCoverageException;
use PHPUnit\Framework\TestResult;
use PHPUnit\Runner\CodeCoverage as PHPUnitCoverage;
use SebastianBergmann\CodeCoverage\Filter as CodeCoverageFilter;

trait CodeCoverage
{
    abstract public function getTestResultObject(): TestResult;

    public function codeCoverageStart(): void
    {
        $testResult = $this->getTestResultObject();
        if (method_exists($testResult, 'getCodeCoverage')) {
            // PHPUnit 9
            $codeCoverage = $testResult->getCodeCoverage();
            if (!$codeCoverage) {
                return;
            }
        } else {
            // PHPUnit 10
            if (!PHPUnitCoverage::isActive()) {
                return;
            }
            $codeCoverage = PHPUnitCoverage::instance();
        }

        $codeCoverage->start(Descriptor::getTestSignature($this));
    }

    public function codeCoverageEnd(string $status, float $time): void
    {
        $testResult = $this->getTestResultObject();
        if (method_exists($testResult, 'getCodeCoverage')) {
            // PHPUnit 9
            $codeCoverage = $testResult->getCodeCoverage();
            if (!$codeCoverage) {
                return;
            }
        } else {
            // PHPUnit 10
            if (!PHPUnitCoverage::isActive()) {
                return;
            }
            $codeCoverage = PHPUnitCoverage::instance();
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
        } catch (CodeCoverageException $exception) {
            if ($status === CodeceptTest::STATUS_OK) {
                $this->getTestResultObject()->addError($this, $exception, $time);
            }
        }
    }
}
