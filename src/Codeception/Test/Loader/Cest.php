<?php

declare(strict_types=1);

namespace Codeception\Test\Loader;

use Codeception\Lib\Parser;
use Codeception\Test\Cest as CestFormat;
use Codeception\Test\DataProvider;
use Codeception\Util\Annotation;
use ReflectionClass;

use function get_class_methods;

class Cest implements LoaderInterface
{
    /**
     * @var CestFormat[]
     */
    protected array $tests = [];

    /**
     * @return CestFormat[]
     */
    public function getTests(): array
    {
        return $this->tests;
    }

    public function getPattern(): string
    {
        return '~Cest\.php$~';
    }

    public function loadTests(string $filename): void
    {
        Parser::load($filename);
        $testClasses = Parser::getClassesFromFile($filename);

        foreach ($testClasses as $testClass) {
            if (!str_ends_with($testClass, 'Cest')) {
                continue;
            }
            if (!(new ReflectionClass($testClass))->isInstantiable()) {
                continue;
            }
            $unit = new $testClass();

            $methods = get_class_methods($testClass);

            $beforeClassMethods = [];
            $afterClassMethods = [];

            foreach ($methods as $methodName) {
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

            foreach ($methods as $method) {
                if (str_starts_with($method, '_')) {
                    continue;
                }

                $examples = DataProvider::getDataForMethod(new \ReflectionMethod($testClass, $method));

                if ($examples === null) {
                    $test = new CestFormat($unit, $method, $filename);
                    $this->tests[] = $test;
                } else {
                    foreach ($examples as $i => $example) {
                        $test = new CestFormat($unit, $method, $filename);
                        $test->getMetadata()->setCurrent(['example' => $example]);
                        $test->getMetadata()->setIndex($i);
                        $this->tests[] = $test;
                    }
                }
            }

            $test->getMetadata()->setBeforeClassMethods($beforeClassMethods);
            $test->getMetadata()->setAfterClassMethods($afterClassMethods);
        }
    }
}
