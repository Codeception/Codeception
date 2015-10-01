<?php
namespace Codeception\Test\Loader;

use Codeception\Lib\Parser;
use Codeception\Test\Cest as CestFormat;
use Codeception\Util\Annotation;

class Cest implements LoaderInterface
{
    protected $tests = [];

    public function getTests()
    {
        return $this->tests;
    }

    public function getPattern()
    {
        return '~Cest\.php$~';
    }

    public function loadTests($file)
    {
        Parser::load($file);
        $testClasses = Parser::getClassesFromFile($file);

        foreach ($testClasses as $testClass) {
            if (substr($testClass, -strlen('Cest')) !== 'Cest') {
                continue;
            }
            if (!(new \ReflectionClass($testClass))->isInstantiable()) {
                continue;
            }

            $unit = new $testClass;

            $methods = get_class_methods($testClass);
            foreach ($methods as $method) {
                $test = $this->createTestForMethod($unit, $method, $file);
                if ($test) {
                    $this->tests[] = $test;
                }
            }
        }
    }

    protected function createTestForMethod($cestInstance, $methodName, $file)
    {
        if (strpos($methodName, '_') === 0) {
            return null;
        }

        return new CestFormat($cestInstance, $methodName, $file);
    }
}