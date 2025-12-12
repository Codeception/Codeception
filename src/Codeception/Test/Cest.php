<?php

declare(strict_types=1);

namespace Codeception\Test;

use Codeception\Example;
use Codeception\Exception\ConfigurationException;
use Codeception\Exception\UselessTestException;
use Codeception\Lib\Console\Message;
use Codeception\Lib\Di;
use Codeception\Lib\Parser;
use Codeception\Step\Comment;
use Codeception\Util\Annotation;
use Codeception\Util\ReflectionHelper;
use Exception;
use LogicException;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\SkippedTest;
use PHPUnit\Metadata\Api\CodeCoverage;
use PHPUnit\Runner\Version as PHPUnitVersion;
use PHPUnit\Util\Test as TestUtil;
use ReflectionMethod;
use SebastianBergmann\CodeCoverage\Version as CodeCoverageVersion;

use function array_slice;
use function file;
use function implode;
use function is_callable;
use function preg_replace;
use function sprintf;
use function strtolower;

/**
 * Executes tests delivered in Cest format.
 *
 * Handles loading of Cest cases, executing specific methods, following the order from `#Before` and `#After` attributes.
 */
class Cest extends Test implements
    Interfaces\ScenarioDriven,
    Interfaces\Reported,
    Interfaces\Dependent,
    Interfaces\StrictCoverage
{
    use Feature\ScenarioLoader;

    protected Parser $parser;

    protected object $testInstance;

    protected string $testClass;

    protected string $testMethod;

    public function __construct(object $testInstance, string $methodName, string $fileName)
    {
        $metadata = new Metadata();
        $metadata->setName($methodName);
        $metadata->setFilename($fileName);
        $classAnnotations = Annotation::forClass($testInstance);
        $metadata->setParamsFromAnnotations($classAnnotations->raw());
        $metadata->setParamsFromAttributes($classAnnotations->attributes());
        $methodAnnotations = Annotation::forMethod($testInstance, $methodName);
        $metadata->setParamsFromAnnotations($methodAnnotations->raw());
        $metadata->setParamsFromAttributes($methodAnnotations->attributes());
        $this->setMetadata($metadata);
        $this->testInstance = $testInstance;
        $this->testClass    = $testInstance::class;
        $this->testMethod   = $methodName;
        $this->createScenario();
        $this->parser = new Parser($this->getScenario(), $this->getMetadata());
    }

    public function __clone(): void
    {
        $this->scenario = clone $this->scenario;
    }

    public function preload(): void
    {
        $this->scenario->setFeature($this->getSpecFromMethod());
        $this->parser->parseFeature($this->getSourceCode());
        $this->getDiService()->injectDependencies($this->testInstance);

        if ($example = $this->getMetadata()->getCurrent('example')) {
            $step = new Comment('', $example);
            $this->scenario->setFeature(
                $this->scenario->getFeature() . ' | ' . $step->getArgumentsAsString(100)
            );
        }
    }

    public function getSourceCode(): string
    {
        $method = new ReflectionMethod($this->testInstance, $this->testMethod);
        $startLine = $method->getStartLine() - 1; // it's actually - 1, otherwise you wont get the function() block
        $lines = file($method->getFileName());
        return implode(
            '',
            array_slice($lines, $startLine, $method->getEndLine() - $startLine)
        );
    }

    public function getSpecFromMethod(): string
    {
        $text = $this->testMethod;
        $text = preg_replace('#([A-Z]+)([A-Z][a-z])#', '\\1 \\2', $text);
        $text = preg_replace('#([a-z\d])([A-Z])#', '\\1 \\2', $text);
        return strtolower($text);
    }

    public function test(): void
    {
        $actorClass = $this->getMetadata()->getCurrent('actor')
            ?? throw new ConfigurationException(
                'actor setting is missing in suite configuration. Replace `class_name` with `actor` in config to fix this'
            );

        $di = $this->getDiService();
        $di->set($this->getScenario());
        $I  = $di->instantiate($actorClass);

        try {
            $this->executeHook($I, 'before');
            $this->executeBeforeMethods($this->testMethod, $I);
            $this->executeTestMethod($I);
            $this->executeAfterMethods($this->testMethod, $I);
            $this->executeHook($I, 'passed');
        } catch (IncompleteTestError | SkippedTest | UselessTestException $exception) {
            // don't call failed hook
            throw $exception;
        } catch (Exception $exception) {
            $this->executeHook($I, 'failed');
            throw $exception;
        } finally {
            $this->executeHook($I, 'after');
        }
    }

    protected function executeHook($I, string $hook): void
    {
        if (is_callable([$this->testInstance, "_{$hook}"])) {
            $this->invoke("_{$hook}", [$I, $this->scenario]);
        }
    }

    protected function executeBeforeMethods(string $testMethod, $I): void
    {
        $methods = Annotation::forMethod($this->testClass, $testMethod)->fetchAll('before');
        foreach ($methods as $method) {
            $this->executeContextMethod(trim($method), $I);
        }
    }

    protected function executeAfterMethods(string $testMethod, $I): void
    {
        $methods = Annotation::forMethod($this->testClass, $testMethod)->fetchAll('after');
        foreach ($methods as $method) {
            $this->executeContextMethod(trim($method), $I);
        }
    }

    protected function executeContextMethod(string $context, $I): void
    {
        if (method_exists($this->testInstance, $context)) {
            $this->executeBeforeMethods($context, $I);
            $this->invoke($context, [$I, $this->scenario]);
            $this->executeAfterMethods($context, $I);
            return;
        }
        throw new LogicException(
            "Method {$context} defined in annotation but does not exist in " . $this->testClass
        );
    }

    protected function invoke(string $methodName, array $context): void
    {
        foreach ($context as $class) {
            $this->getDiService()->set($class);
        }
        $this->getDiService()->injectDependencies($this->testInstance, $methodName, $context);
    }

    protected function executeTestMethod($I): void
    {
        if (!method_exists($this->testInstance, $this->testMethod)) {
            throw new Exception("Method {$this->testMethod} can't be found in tested class");
        }

        if ($example = $this->getMetadata()->getCurrent('example')) {
            $this->invoke($this->testMethod, [$I, $this->scenario, new Example($example)]);
        } else {
            $this->invoke($this->testMethod, [$I, $this->scenario]);
        }
    }

    public function toString(): string
    {
        return sprintf(
            '%s: %s',
            ReflectionHelper::getClassShortName($this->getTestInstance()),
            Message::ucfirst($this->getFeature()),
        );
    }

    public function getSignature(): string
    {
        return "{$this->testClass}:{$this->testMethod}";
    }

    public function getTestInstance(): object
    {
        return $this->testInstance;
    }

    public function getTestMethod(): string
    {
        return $this->testMethod;
    }

    public function getReportFields(): array
    {
        return [
            'name'    => $this->testMethod,
            'class'   => $this->testClass,
            'file'    => $this->getFileName(),
            'feature' => $this->getFeature(),
        ];
    }

    protected function getParser(): Parser
    {
        return $this->parser;
    }

    public function fetchDependencies(): array
    {
        $names = [];
        foreach ($this->getMetadata()->getDependencies() as $dependency) {
            foreach ((array)$dependency as $required) {
                if (!str_contains($required, ':') && method_exists($this->getTestInstance(), $required)) {
                    $required = $this->testClass . ":{$required}";
                }
                $names[] = $required;
            }
        }
        return $names;
    }

    public function getLinesToBeCovered(): array|bool
    {
        if (PHPUnitVersion::series() < 10) {
            return TestUtil::getLinesToBeCovered($this->testClass, $this->testMethod);
        }

        if (version_compare(CodeCoverageVersion::id(), '12', '>=')) {
            return (new CodeCoverage())->coversTargets($this->testClass, $this->testMethod)->asArray();
        }

        return (new CodeCoverage())->linesToBeCovered($this->testClass, $this->testMethod);
    }

    public function getLinesToBeUsed(): array
    {
        if (PHPUnitVersion::series() < 10) {
            return TestUtil::getLinesToBeUsed($this->testClass, $this->testMethod);
        }

        if (version_compare(CodeCoverageVersion::id(), '12', '>=')) {
            return (new CodeCoverage())->usesTargets($this->testClass, $this->testMethod)->asArray();
        }

        return (new CodeCoverage())->linesToBeUsed($this->testClass, $this->testMethod);
    }

    private function getDiService(): Di
    {
        return $this->getMetadata()->getService('di');
    }
}
