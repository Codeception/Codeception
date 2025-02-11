<?php

declare(strict_types=1);

namespace Codeception\Test\Feature;

use Codeception\Coverage\PhpCodeCoverageFactory;
use Codeception\Event\FailEvent;
use Codeception\ResultAggregator;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\StrictCoverage;
use Codeception\Test\Test;
use Codeception\Test\Test as CodeceptTest;
use PHPUnit\Runner\Version as PHPUnitVersion;
use SebastianBergmann\CodeCoverage\Exception as CodeCoverageException;
use SebastianBergmann\CodeCoverage\Test\Target\TargetCollection;
use SebastianBergmann\CodeCoverage\Test\TestStatus\TestStatus;
use SebastianBergmann\CodeCoverage\Version as CodeCoverageVersion;

trait CodeCoverage
{
    abstract public function getResultAggregator(): ResultAggregator;

    public function codeCoverageStart(): void
    {
        $codeCoverage = PhpCodeCoverageFactory::build();
        $codeCoverage->start(Descriptor::getTestSignature($this));
    }

    public function codeCoverageEnd(string $status, float $time): void
    {
        $codeCoverage = PhpCodeCoverageFactory::build();

        if ($this instanceof StrictCoverage) {
            $linesToBeCovered = $this->getLinesToBeCovered();
            $linesToBeUsed = $this->getLinesToBeUsed();
        } else {
            $linesToBeCovered = [];
            $linesToBeUsed = [];
        }

        try {
            if (PHPUnitVersion::series() < 10) {
                $codeCoverage->stop(true, $linesToBeCovered, $linesToBeUsed);
            } else {
                $status = match ($status) {
                    Test::STATUS_OK => TestStatus::success(),
                    Test::STATUS_FAIL, Test::STATUS_ERROR => TestStatus::failure(),
                    default => TestStatus::unknown(),
                };
                if (version_compare(CodeCoverageVersion::id(), '12', '>=')) {
                    if (is_array($linesToBeCovered)) {
                        $linesToBeCovered = TargetCollection::fromArray($linesToBeCovered);
                    }
                    $linesToBeUsed = TargetCollection::fromArray($linesToBeUsed);
                }
                $codeCoverage->stop(true, $status, $linesToBeCovered, $linesToBeUsed);
            }
        } catch (CodeCoverageException $exception) {
            if ($status === CodeceptTest::STATUS_OK) {
                $this->getResultAggregator()->addError(new FailEvent($this, $exception, $time));
            }
        }
    }
}
