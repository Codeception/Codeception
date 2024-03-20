<?php

declare(strict_types=1);

namespace Codeception\Test\Loader;

use Codeception\Lib\Parser;
use Codeception\Test\DataProvider;
use Codeception\Test\TestCaseWrapper;
use Codeception\Util\Annotation;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version as PHPUnitVersion;
use PHPUnit\Util\Test as TestUtil;
use ReflectionClass;
use ReflectionMethod;

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

            // find hook methods
            $beforeClassMethods = ['setUpBeforeClass'];
            $afterClassMethods = ['tearDownAfterClass'];

            foreach ($reflected->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $methodName = $method->getName();
                $methodAnnotations = Annotation::forMethod($testClass, $methodName);

                $beforeClassAnnotation = $methodAnnotations->fetch('beforeClass');
                if ($beforeClassAnnotation !== null) {
                    $beforeClassMethods [] = $methodName;
                }

                $afterClassAnnotation = $methodAnnotations->fetch('afterClass');
                if ($afterClassAnnotation !== null) {
                    $afterClassMethods [] = $methodName;
                }
            }

            foreach ($reflected->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $tests = $this->createTestsFromPhpUnitMethod($reflected, $method);

                foreach ($tests as $test) {
                    $this->tests[] = new TestCaseWrapper($test, $beforeClassMethods, $afterClassMethods);
                    // only the first instance gets before/after class methods
                    $beforeClassMethods = $afterClassMethods = [];
                }
            }
        }
    }

    public function getTests(): array
    {
        return $this->tests;
    }

    /**
     * @return TestCase[]
     */
    protected function createTestsFromPhpUnitMethod(ReflectionClass $class, ReflectionMethod $method): array
    {
        if (!TestUtil::isTestMethod($method)) {
            return [];
        }
        $className = $class->getName();
        $methodName = $method->getName();

        $data = DataProvider::getDataForMethod($method, $class);

        if (!isset($data)) {
            return [ new $className($methodName) ];
        }

        $result = [];
        foreach ($data as $key => $item) {
            if (PHPUnitVersion::series() < 10) {
                $testInstance = new $className($methodName, $item, $key);
            } else {
                $testInstance = new $className($methodName);
                $testInstance->setData($key, $item);
            }
            $result [] = $testInstance;
        }

        return $result;
    }

    /**
     * @param string[] $beforeClassMethods
     * @param string[] $afterClassMethods
     */
    protected function enhancePhpunitTest(
        TestCase $testCase,
        array $beforeClassMethods,
        array $afterClassMethods,
    ): TestCaseWrapper {

        return new TestCaseWrapper($testCase, $beforeClassMethods, $afterClassMethods);
    }
}
