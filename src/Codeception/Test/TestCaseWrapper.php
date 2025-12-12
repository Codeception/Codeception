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
        array $afterClassMethods = []
    ) {
        $this->metadata = new Metadata();
        $methodName = PHPUnitVersion::series() < 10
            ? $testCase->getName(false)
            : $testCase->name();
        $this->metadata->setName($methodName);
        $this->metadata->setFilename((new ReflectionClass($testCase))->getFileName());

        if ($testCase->dataName() !== '') {
            $this->metadata->setIndex($testCase->dataName());
        }

        $classAnnotations = Annotation::forClass($testCase);
        $this->metadata->setParamsFromAnnotations($classAnnotations->raw());
        $this->metadata->setParamsFromAttributes($classAnnotations->attributes());

        $methodAnnotations = Annotation::forMethod($testCase, $methodName);
        $this->metadata->setParamsFromAnnotations($methodAnnotations->raw());
        $this->metadata->setParamsFromAttributes($methodAnnotations->attributes());

        $this->metadata->setBeforeClassMethods($beforeClassMethods);
        $this->metadata->setAfterClassMethods($afterClassMethods);
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
        return $this->testCase instanceof Unit
            ? $this->testCase->getScenario()
            : null;
    }

    public function fetchDependencies(): array
    {
        $class = $this->testCase::class;
        return array_map(
            static fn(string $dep): string => str_contains($dep, ':') || !method_exists($class, $dep)
                ? $dep
                : "$class:$dep",
            $this->metadata->getDependencies()
        );
    }

    public function getReportFields(): array
    {
        return [
            'name'  => $this->getNameWithDataSet(),
            'class' => $this->testCase::class,
            'file'  => $this->metadata->getFilename(),
        ];
    }

    public function getLinesToBeCovered(): array|bool
    {
        if (
            version_compare(PHPUnitVersion::series(), '10.0', '<')
            && method_exists(TestUtil::class, 'getLinesToBeCovered')
        ) {
            return TestUtil::getLinesToBeCovered($this->testCase::class, $this->metadata->getName());
        }
        return $this->coverageTargets('coversTargets', 'linesToBeCovered');
    }

    public function getLinesToBeUsed(): array
    {
        if (
            version_compare(PHPUnitVersion::series(), '10.0', '<')
            && method_exists(TestUtil::class, 'getLinesToBeUsed')
        ) {
            return TestUtil::getLinesToBeUsed($this->testCase::class, $this->metadata->getName());
        }
        return (array) $this->coverageTargets('usesTargets', 'linesToBeUsed');
    }

    public function test(): void
    {
        $inputs = array_map(
            fn(string $dep) => self::$testResults[$dep] ?? null,
            $this->fetchDependencies()
        );
        $this->testCase->setDependencyInput($inputs);
        $this->testCase->runBare();
        $this->testCase->addToAssertionCount(Assert::getCount());

        self::$testResults[$this->getSignature()] = PHPUnitVersion::series() < 10
            ? $this->testCase->getResult()
            : $this->testCase->result();

        $assertions = $this->getNumAssertions();
        if (
            $this->reportUselessTests &&
            $assertions > 0 &&
            $this->doesNotPerformAssertions()
        ) {
            throw new UselessTestException(
                sprintf(
                    'This test indicates it does not perform assertions but %d assertions were performed',
                    $assertions
                )
            );
        }
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
        return PHPUnitVersion::series() < 10
            ? $this->testCase->getName(true)
            : $this->testCase->nameWithDataSet();
    }

    private function coverageTargets(string $newMethod, string $legacyMethod): array|bool
    {
        $coverage = new CodeCoverage();
        return version_compare(CodeCoverageVersion::id(), '12', '>=')
            ? $coverage->$newMethod($this->testCase::class, $this->metadata->getName())->asArray()
            : $coverage->$legacyMethod($this->testCase::class, $this->metadata->getName());
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
        return PHPUnitVersion::series() < 10
            ? $this->testCase->getNumAssertions()
            : $this->testCase->numberOfAssertionsPerformed();
    }
}
