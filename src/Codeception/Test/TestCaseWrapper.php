<?php

declare(strict_types=1);

namespace Codeception\Test;

use Codeception\ResultAggregator;
use Codeception\Test\Interfaces\Dependent;
use Codeception\Test\Interfaces\Descriptive;
use Codeception\Test\Interfaces\Reported;
use Codeception\Test\Interfaces\StrictCoverage;
use Codeception\TestInterface;
use Codeception\Util\Annotation;
use Codeception\Util\ReflectionHelper;
use PHPUnit\Framework\ErrorTestCase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Api\CodeCoverage;
use PHPUnit\Runner\Version as PHPUnitVersion;
use PHPUnit\Util\Test as TestUtil;
use ReflectionClass;

/**
 * Wrapper for TestCase tests behaving like native Codeception test format
 */
class TestCaseWrapper extends Test implements Reported, Dependent, StrictCoverage, TestInterface, Descriptive
{
    private Metadata $metadata;

    private ?ResultAggregator $resultAggregator = null;

    /**
     * @param string[] $beforeClassMethods
     * @param string[] $afterClassMethods
     */
    public function __construct(
        private TestCase $testCase,
        array $beforeClassMethods = [],
        array $afterClassMethods = [],
    ) {
        $this->metadata = new Metadata();
        $metadata = $this->metadata;

        $methodName = $testCase->getName(false);
        $metadata->setName($methodName);
        $metadata->setFilename((new ReflectionClass($testCase))->getFileName());

        if ($testCase->dataName() !== '') {
            $metadata->setIndex($testCase->dataName());
        }

        $methodName = $testCase->getName(false);

        if ($testCase instanceof ErrorTestCase) {
            return;
        }
        $classAnnotations = Annotation::forClass($testCase);
        $metadata->setParamsFromAnnotations($classAnnotations->raw());
        $metadata->setParamsFromAttributes($classAnnotations->attributes());

        $methodAnnotations = Annotation::forMethod($testCase, $methodName);
        $metadata->setParamsFromAnnotations($methodAnnotations->raw());
        $metadata->setParamsFromAttributes($methodAnnotations->attributes());

        $metadata->setBeforeClassMethods($beforeClassMethods);
        $metadata->setAfterClassMethods($afterClassMethods);
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

    protected function doesNotPerformAssertions(): bool
    {
        return $this->testCase->doesNotPerformAssertions() || parent::doesNotPerformAssertions();
    }
}
