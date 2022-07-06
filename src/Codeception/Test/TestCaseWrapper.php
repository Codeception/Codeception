<?php

declare(strict_types=1);

namespace Codeception\Test;

use Codeception\ResultAggregator;
use Codeception\Test\Interfaces\Dependent;
use Codeception\Test\Interfaces\Descriptive;
use Codeception\Test\Interfaces\Reported;
use Codeception\Test\Interfaces\StrictCoverage;
use Codeception\TestInterface;
use Codeception\Util\ReflectionHelper;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\RiskyDueToUnexpectedAssertionsException;
use PHPUnit\Framework\RiskyTestError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Api\CodeCoverage;
use PHPUnit\Runner\Version as PHPUnitVersion;
use PHPUnit\Util\Test as TestUtil;

/**
 * Wrapper for TestCase tests behaving like native Codeception test format
 */
class TestCaseWrapper extends Test implements Reported, Dependent, StrictCoverage, TestInterface, Descriptive
{
    private Metadata $metadata;

    private ?ResultAggregator $resultAggregator = null;

    public function __construct(private TestCase $testCase)
    {
        $this->metadata = new Metadata();
    }

    public function getTestCase(): TestCase
    {
        return $this->testCase;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function getResultAggregator(): ResultAggregator
    {
        if ($this->resultAggregator === null) {
            throw new \LogicException('ResultAggregator is not set');
        }
        return $this->resultAggregator;
    }

    public function setResultAggregator(?ResultAggregator $resultAggregator): void
    {
        $this->resultAggregator = $resultAggregator;
    }


    public function fetchDependencies(): array
    {
        $names = [];
        foreach ($this->metadata->getDependencies() as $required) {
            if (!str_contains($required, ':') && method_exists($this->testCase::class, $required)) {
                $required = $this->testCase::class . ':' . $required;
            }
            $names[] = $required;
        }
        return $names;
    }

    /**
     * @return array<string, string>
     */
    public function getReportFields(): array
    {
        return [
            'name'    => $this->testCase->getName(true),
            'class'   => $this->testCase::class,
            'file'    => $this->metadata->getFilename()
        ];
    }

    public function getLinesToBeCovered(): array
    {
        $class = $this->testCase::class;
        $method = $this->metadata->getName();

        if (PHPUnitVersion::series() < 10) {
            return TestUtil::getLinesToBeCovered($class, $method);
        }
        return (new CodeCoverage())->linesToBeCovered($class, $method);
    }

    public function getLinesToBeUsed(): array
    {
        $class = $this->testCase::class;
        $method = $this->metadata->getName();

        if (PHPUnitVersion::series() < 10) {
            return TestUtil::getLinesToBeUsed($class, $method);
        }
        return (new CodeCoverage())->linesToBeUsed($class, $method);
    }

    public function test(): void
    {
        $this->testCase->runBare();

        $numberOfAssertionsPerformed = Assert::getCount();
        if (
            $this->reportUselessTests &&
            $numberOfAssertionsPerformed > 0 &&
            $this->testCase->doesNotPerformAssertions()
        ) {
            if (PHPUnitVersion::series() < 10) {
                throw new RiskyTestError(
                    sprintf(
                        'This test is annotated with "@doesNotPerformAssertions" but performed %d assertions',
                        $numberOfAssertionsPerformed
                    )
                );
            } else {
                throw new RiskyDueToUnexpectedAssertionsException(
                    $numberOfAssertionsPerformed
                );
            }
        }
    }

    public function toString(): string
    {
        $text = Descriptor::getTestCaseNameAsString($this->testCase->getName(true));
        return ReflectionHelper::getClassShortName($this->testCase) . ': ' . $text;
    }

    public function getFileName(): string
    {
        return $this->metadata->getFilename();
    }

    public function getSignature(): string
    {
        return $this->testCase::class . ':' . $this->metadata->getName();
    }
}
