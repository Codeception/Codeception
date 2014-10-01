<?php
namespace Codeception;

use Codeception\Lib\Parser;
use Codeception\TestCase\Cept;
use Codeception\TestCase\Cest;
use Codeception\Util\Annotation;
use Symfony\Component\Finder\Finder;

/**
 * Loads all Codeception supported test formats from a directory.
 *
 * ``` php
 * <?php
 * $testLoader = new \Codeception\TestLoader('tests/unit');
 * $testLoader->loadTests();
 * $tests = $testLoader->getTests();
 * ?>
 * ```
 * You can load specific file
 *
 * ``` php
 * <?php
 * $testLoader = new \Codeception\TestLoader('tests/unit');
 * $testLoader->loadTest('UserTest.php');
 * $testLoader->loadTest('PostTest.php');
 * $tests = $testLoader->getTests();
 * ?>
 * ```
 * or a subdirectory
 *
 * ``` php
 * <?php
 * $testLoader = new \Codeception\TestLoader('tests/unit');
 * $testLoader->loadTest('models'); // all tests from tests/unit/models
 * $tests = $testLoader->getTests();
 * ?>
 * ```
 *
 */
class TestLoader {

    protected static $formats = array('Cest', 'Cept', 'Test');
    protected $tests = [];
    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function getTests()
    {
        $total = array_keys($this->tests);
        $tests = array();
        do {
            $i = array_rand($total, 1);
            $tests[] = $this->tests[$i];
            unset($total[$i]);
        } while(count($tests) < count($this->tests));
        return $tests;

    }
    

    protected function relativeName($file)
    {
        return $name = str_replace([$this->path, '\\'], ['', '/'], $file);
    }

    public function loadTest($path)
    {
        $path = $this->path . $this->relativeName($path);
        if (!file_exists($path)) {
            throw new \Exception("File $path not found");
        }

        foreach (self::$formats as $format) {
            if (preg_match("~$format.php$~", $path)) {
                call_user_func(array($this, "add$format"), $path);
                return;
            }
        }

        if (is_dir($path)) {
            $currentPath = $this->path;
            $this->path = $path;
            $this->loadTests();
            $this->path = $currentPath;
            return;
        }
        throw new \Exception('Test format not supported. Please, check you use the right suffix. Available filetypes: Cept, Cest, Test');
    }

    public function loadTests()
    {
        $finder = Finder::create()->files()->sortByName()->in($this->path);

        foreach (self::$formats as $format) {
            $formatFinder = clone($finder);
            $testFiles = $formatFinder->name("*$format.php");
            foreach ($testFiles as $test) {
                call_user_func(array($this, "add$format"), $test->getPathname());
            }
        }
    }

    public function addTest($path)
    {
        $testClasses = Parser::getClassesFromFile($path);

        foreach ($testClasses as $testClass) {
            $reflected = new \ReflectionClass($testClass);
            if ($reflected->isAbstract()) {
                continue;
            }

            foreach ($reflected->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $test = $this->createTestFromPhpUnitMethod($reflected, $method);
                if (!$test) {
                    continue;
                }
                $this->tests[] = $test;
            }
        }
    }

    public function addCept($file)
    {
        $name = $this->relativeName($file);

        $cept = new Cept();
        $cept->configName($name)
            ->configFile($file)
            ->initConfig();

        $this->tests[] = $cept;
    }

    public function addCest($file)
    {
        $testClasses = Parser::getClassesFromFile($file);

        foreach ($testClasses as $testClass) {
            $reflected = new \ReflectionClass($testClass);
            if ($reflected->isAbstract()) {
                continue;
            }

            $unit = new $testClass;
            $methods = get_class_methods($testClass);
            foreach ($methods as $method) {
                $test = $this->createTestFromCestMethod($unit, $method, $file);
                if (!$test) {
                    continue;
                }
                $this->tests[] = $test;
            }
        }
    }


    protected function createTestFromPhpUnitMethod(\ReflectionClass $class, \ReflectionMethod $method)
    {
        if (!\PHPUnit_Framework_TestSuite::isTestMethod($method)) {
            return;
        }
        $test = \PHPUnit_Framework_TestSuite::createTest($class, $method->name);

        if ($test instanceof \PHPUnit_Framework_TestSuite_DataProvider) {
            foreach ($test->tests() as $t) {
                $this->enhancePhpunitTest($t);
            }
            return $test;
        }

        $this->enhancePhpunitTest($test);
        return $test;
    }

    protected function enhancePhpunitTest(\PHPUnit_Framework_TestCase $test)
    {
        $className = get_class($test);
        $methodName = $test->getName(false);
        $test->setDependencies(\PHPUnit_Util_Test::getDependencies($className, $methodName));

        if (!$test instanceof \Codeception\TestCase) {
            return;
        }
        $test->initConfig();
        $test->getScenario()->env(Annotation::forMethod($className, $methodName)->fetchAll('env'));
    }

    protected function createTestFromCestMethod($cestInstance, $methodName, $file)
    {
        if ((strpos($methodName, '_') === 0) or ($methodName == '__construct')) {
            return null;
        }
        $testClass = get_class($cestInstance);

        $cest = new Cest();
        $cest->configName($methodName)
            ->configFile($file)
            ->config('testClassInstance', $cestInstance)
            ->config('testMethod', $methodName)
            ->initConfig();

        $cest->getScenario()->env(Annotation::forMethod($testClass, $methodName)->fetchAll('env'));
        $cest->setDependencies(\PHPUnit_Util_Test::getDependencies($testClass, $methodName));
        return $cest;
    }



} 
