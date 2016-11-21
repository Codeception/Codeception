<?php
namespace Codeception\Test\Loader;

use Codeception\Exception\TestParseException;
use Codeception\Lib\ExampleSuite;
use Codeception\Lib\Parser;
use Codeception\Test\Cest as CestFormat;
use Codeception\Test\Descriptor;
use Codeception\Util\Annotation;
use Codeception\Util\ReflectionHelper;

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
                if (strpos($method, '_') === 0) {
                    continue;
                }
                $examples = [];

                // example Annotation
                $rawExamples = Annotation::forMethod($unit, $method)->fetchAll('example');
                if (count($rawExamples)) {
                    $examples = array_map(
                        function ($v) {
                            return Annotation::arrayValue($v);
                        },
                        $rawExamples
                    );
                }

                // dataprovider Annotation
                $dataMethod = Annotation::forMethod($testClass, $method)->fetch('dataprovider');
                if (!empty($dataMethod)) {
                    try {
                        $data = ReflectionHelper::invokePrivateMethod($unit, $dataMethod);
                        // allow to mix example and dataprovider annotations
                        $examples = array_merge($examples, $data);
                    } catch (\Exception $e) {
                        throw new TestParseException(
                            $file,
                            "DataProvider '$dataMethod' for $testClass->$method is invalid or not callable.\n" .
                            "Make sure that the dataprovider exist within the test class."
                        );
                    }
                }

                if (count($examples)) {
                    $dataProvider = new \PHPUnit_Framework_TestSuite_DataProvider();
                    foreach ($examples as $k => $example) {
                        if ($example === null) {
                            throw new TestParseException(
                                $file,
                                "Example for $testClass->$method contains invalid data:\n" .
                                $rawExamples[$k] . "\n" .
                                "Make sure this is a valid JSON (Hint: \"-char for strings) or a single-line annotation in Doctrine-style"
                            );
                        }
                        $test = new CestFormat($unit, $method, $file);
                        $test->getMetadata()->setCurrent(['example' => $example]);
                        $dataProvider->addTest($test);
                    }
                    $this->tests[] = $dataProvider;
                    continue;
                }
                $this->tests[] = new CestFormat($unit, $method, $file);
            }
        }
    }
}
