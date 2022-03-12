<?php

declare(strict_types=1);

namespace Codeception\Test\Feature;

use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\StrictCoverage;
use Codeception\Test\Test as CodeceptTest;
use PHPUnit\Framework\TestResult;
use PHPUnit\Runner\CodeCoverage as PHPUnitCoverage;
use SebastianBergmann\CodeCoverage\Exception as CodeCoverageException;

use function class_exists;

trait CodeCoverage
{
    abstract public function getTestResultObject(): TestResult;

    public function codeCoverageStart(): void
    {
        if (class_exists(PHPUnitCoverage::class)) {
            // PHPUnit 10+
            if (!PHPUnitCoverage::isActive()) {
                return;
            }
            $codeCoverage = PHPUnitCoverage::instance();
        } else {
            // PHPUnit 9
            $codeCoverage = $this->getTestResultObject()->getCodeCoverage();
            if (!$codeCoverage) {
                return;
            }
        }

        $codeCoverage->start(Descriptor::getTestSignature($this));
    }

    public function codeCoverageEnd(string $status, float $time): void
    {
        if (class_exists(PHPUnitCoverage::class)) {
            // PHPUnit 10+
            if (!PHPUnitCoverage::isActive()) {
                return;
            }
            $codeCoverage = PHPUnitCoverage::instance();
        } else {
            // PHPUnit 9
            $codeCoverage = $this->getTestResultObject()->getCodeCoverage();
            if (!$codeCoverage) {
                return;
            }
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
