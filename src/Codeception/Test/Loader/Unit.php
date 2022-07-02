<?php

declare(strict_types=1);

namespace Codeception\Test\Loader;

use Codeception\Lib\Parser;
use Codeception\Test\DataProvider;
use Codeception\Test\Descriptor;
use Codeception\Test\Unit as UnitFormat;
use Codeception\Util\Annotation;
use PHPUnit\Framework\ErrorTestCase;
use PHPUnit\Framework\IncompleteTestCase;
use PHPUnit\Framework\SkippedTestCase;
use PHPUnit\Framework\Test as PHPUnitTest;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Api\Dependencies;
use PHPUnit\Runner\Version as PHPUnitVersion;
use PHPUnit\Util\Test as TestUtil;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

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
                $tests = $this->createTestsFromPhpUnitMethod($reflected, $method);

                foreach ($tests as $test) {
                    $this->enhancePhpunitTest($test);
                    $this->tests[] = $test;
                }
            }
        }
    }

    public function getTests(): array
    {
        return $this->tests;
    }

    /**
     * @return PHPUnitTest[]
     */
    protected function createTestsFromPhpUnitMethod(ReflectionClass $class, ReflectionMethod $method): array
    {
        if (!TestUtil::isTestMethod($method)) {
            return [];
        }
        $className = $class->getName();
        $methodName = $method->getName();

        try {
            $data = DataProvider::getDataForMethod($method);
        } catch (Throwable $t) {
            $message = sprintf(
                "The data provider specified for %s::%s is invalid.\n%s",
                $className,
                $methodName,
                $t->getMessage(),
            );

            if (PHPUnitVersion::series() < 10) {
                $data = new ErrorTestCase($message);
            } else {
                $data = new ErrorTestCase($className, $methodName, $message);
            }
        }

        if (!isset($data)) {
            return [ new $className($methodName) ];
        }

        if (
            $data instanceof ErrorTestCase ||
            $data instanceof SkippedTestCase ||
            $data instanceof IncompleteTestCase
        ) {
            return [ $data ];
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

    protected function enhancePhpunitTest(PHPUnitTest $test): void
    {
        if (!$test instanceof TestCase || $test instanceof ErrorTestCase) {
            return;
        }
        $className = get_class($test);
        $methodName = $test->getName(false);

        if (PHPUnitVersion::series() < 10) {
            $dependencies = TestUtil::getDependencies($className, $methodName);
        } else {
            $dependencies = Dependencies::dependencies($className, $methodName);
        }

        $test->setDependencies($dependencies);
        if ($test instanceof UnitFormat) {
            $annotations = Annotation::forMethod($test, $methodName)->raw();
            $test->getMetadata()->setParamsFromAnnotations($annotations);
            $test->getMetadata()->setParamsFromAttributes(Annotation::forMethod($test, $methodName)->attributes());
            $test->getMetadata()->setFilename(Descriptor::getTestFileName($test));
        }
    }
}
