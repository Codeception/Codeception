<?php

declare(strict_types=1);

namespace Codeception\Test;

use Codeception\Exception\UselessTestException;
use Codeception\Scenario;
use Codeception\Test\Interfaces\Dependent;
use Codeception\Test\Interfaces\Descriptive;
use Codeception\Test\Interfaces\Reported;
use Codeception\Test\Interfaces\StrictCoverage;
use Codeception\TestInterface;
use Codeception\Util\Annotation;
use Codeception\Util\ReflectionHelper;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Api\CodeCoverage;
use PHPUnit\Runner\Version as PHPUnitVersion;
use PHPUnit\Util\Test as TestUtil;
use ReflectionClass;
use SebastianBergmann\CodeCoverage\Version as CodeCoverageVersion;

/**
 * Wrapper for TestCase tests behaving like native Codeception test format
 */
class TestCaseWrapper extends Test implements Reported, Dependent, StrictCoverage, TestInterface, Descriptive
{
    private readonly Metadata $metadata;

    /**
     * @var array<string, mixed>
     */
    private static array $testResults = [];

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

        $methodName = PHPUnitVersion::series() < 10 ? $testCase->getName(false) : $testCase->name();
        $metadata->setName($methodName);
        $metadata->setFilename((new ReflectionClass($testCase))->getFileName());

        if ($testCase->dataName() !== '') {
            $metadata->setIndex($testCase->dataName());
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

    public function __clone(): void
    {
        $this->testCase = clone $this->testCase;
    }

    public function getTestCase(): TestCase
    {
        return $this->testCase;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function getScenario(): ?Scenario
    {
        if ($this->testCase instanceof Unit) {
            return $this->testCase->getScenario();
        }

        return null;
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
            'name'    => $this->getNameWithDataSet(),
            'class'   => $this->testCase::class,
            'file'    => $this->metadata->getFilename()
        ];
    }

    public function getLinesToBeCovered(): array|bool
    {
        $class = $this->testCase::class;
        $method = $this->metadata->getName();

        if (PHPUnitVersion::series() < 10) {
            return TestUtil::getLinesToBeCovered($class, $method);
        }

        if (version_compare(CodeCoverageVersion::id(), '12', '>=')) {
            return (new CodeCoverage())->coversTargets($class, $method)->asArray();
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

        if (version_compare(CodeCoverageVersion::id(), '12', '>=')) {
            return (new CodeCoverage())->usesTargets($class, $method)->asArray();
        }

        return (new CodeCoverage())->linesToBeUsed($class, $method);
    }

    public function test(): void
    {
        $dependencyInput = [];
        foreach ($this->fetchDependencies() as $dependency) {
            $dependencyInput[] = self::$testResults[$dependency] ?? null;
        }
        $this->testCase->setDependencyInput($dependencyInput);
        $this->testCase->runBare();

        $this->testCase->addToAssertionCount(Assert::getCount());

        if (PHPUnitVersion::series() < 10) {
            self::$testResults[$this->getSignature()] = $this->testCase->getResult();
        } else {
            self::$testResults[$this->getSignature()] = $this->testCase->result();
        }

        $numberOfAssertionsPerformed = $this->getNumAssertions();
        if (!$this->reportUselessTests || $numberOfAssertionsPerformed <= 0 || !$this->testCase->doesNotPerformAssertions()) {
            return;
        }
        throw new UselessTestException(
            sprintf(
                'This test indicates it does not perform assertions but %d assertions were performed',
                $numberOfAssertionsPerformed
            )
        );
    }

    /**
     * Is the test expected to not perform assertions with `expectNotToPerformAssertions`?
     */
    protected function doesNotPerformAssertions(): bool
    {
         return $this->testCase->doesNotPerformAssertions();
    }

    public function toString(): string
    {
        $text = Descriptor::getTestCaseNameAsString($this->getNameWithDataSet());
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

    private function getNameWithDataSet(): string
    {
        if (PHPUnitVersion::series() < 10) {
            return $this->testCase->getName(true);
        }

        return $this->testCase->nameWithDataSet();
    }

    /**
     * Override this method from the {@see \Codeception\Test\Feature\AssertionCounter} so that we use PHPUnit's
     * assertion count instead of our own.
     * This is needed because PHPUnit's {@see TestCase} has a {@see TestCase::addToAssertionCount()} method which is
     * both internally and externally used to increase the assertion count. Externally it is called from tearDown
     * methods, for example when using Mockery.
     */
    public function getNumAssertions(): int
    {
        if (PHPUnitVersion::series() < 10) {
            return $this->testCase->getNumAssertions();
        } else {
            return $this->testCase->numberOfAssertionsPerformed();
        }
    }
}
