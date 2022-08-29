<?php

declare(strict_types=1);

namespace Codeception\Test\Loader;

use Codeception\Lib\Parser;
use Codeception\Test\Cest as CestFormat;
use Codeception\Test\DataProvider;
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
            foreach ($methods as $method) {
                if (str_starts_with($method, '_')) {
                    continue;
                }

                $examples = DataProvider::getDataForMethod(
                    new \ReflectionMethod($testClass, $method),
                    new \ReflectionClass($testClass)
                );

                if ($examples === null) {
                    $this->tests[] = new CestFormat($unit, $method, $filename);
                    continue;
                }

                foreach ($examples as $i => $example) {
                    $test = new CestFormat($unit, $method, $filename);
                    $test->getMetadata()->setCurrent(['example' => $example]);
                    $test->getMetadata()->setIndex($i);
                    $this->tests[] = $test;
                }
            }
        }
    }
}
