<?php

declare(strict_types=1);

namespace Codeception\Test\Feature;

use Codeception\Coverage\PhpCodeCoverageFactory;
use Codeception\Event\FailEvent;
use Codeception\ResultAggregator;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\StrictCoverage;
use Codeception\Test\Test as CodeceptTest;
use SebastianBergmann\CodeCoverage\Exception as CodeCoverageException;

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
            $codeCoverage->stop(true, $linesToBeCovered, $linesToBeUsed);
        } catch (CodeCoverageException $exception) {
            if ($status === CodeceptTest::STATUS_OK) {
                $this->getResultAggregator()->addError(new FailEvent($this, $exception, $time));
            }
        }
    }
}
