<?php

declare(strict_types=1);

namespace Codeception\Test\Loader;

use Codeception\Lib\Parser;
use Codeception\Test\Descriptor;
use Codeception\Test\Unit as UnitFormat;
use Codeception\Util\Annotation;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\Test as PHPUnitTest;
use PHPUnit\Framework\TestBuilder;
use PHPUnit\Metadata\Api\Dependencies;
use ReflectionClass;
use ReflectionMethod;
use function get_class;

class Unit implements LoaderInterface
{
    protected array $tests = [];

    public function getPattern(): string
    {
        return '~Test\.php$~';
    }

    public function loadTests(string $filename): void
    {
        Parser::load($filename);
        $testClasses = Parser::getClassesFromFile($filename);

        foreach ($testClasses as $testClass) {
            $reflected = new ReflectionClass($testClass);
            if (!$reflected->isInstantiable()) {
                continue;
            }

            foreach ($reflected->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $test = $this->createTestFromPhpUnitMethod($reflected, $method);
                if (!$test) {
                    continue;
                }
                $this->tests[] = $test;
            }
        }
    }

    public function getTests(): array
    {
        return $this->tests;
    }

    /**
     * @return DataProviderTestSuite|PHPUnitTest|null
     */
    protected function createTestFromPhpUnitMethod(ReflectionClass $class, ReflectionMethod $method)
    {
        if (!\PHPUnit\Util\Test::isTestMethod($method)) {
            return null;
        }
        $test = (new TestBuilder)->build($class, $method->name);

        if ($test instanceof DataProviderTestSuite) {
            foreach ($test->tests() as $t) {
                $this->enhancePhpunitTest($t);
            }
            return $test;
        }

        $this->enhancePhpunitTest($test);
        return $test;
    }

    protected function enhancePhpunitTest(PHPUnitTest $test): void
    {
        $className = get_class($test);
        $methodName = $test->getName(false);
        $dependencies = Dependencies::dependencies($className, $methodName);
        $test->setDependencies($dependencies);
        if ($test instanceof UnitFormat) {
            $annotations = Annotation::forMethod($test, $methodName)->raw();
            $test->getMetadata()->setParamsFromAnnotations($annotations);
            $test->getMetadata()->setFilename(Descriptor::getTestFileName($test));
        }
    }
}
