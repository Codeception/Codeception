<?php

namespace Codeception;

use Symfony\Component\Finder\Finder;
use \Symfony\Component\EventDispatcher\EventDispatcher;


class SuiteManager {

    public static $modules = array();
    public static $actions = array();

    /**
     * @var \PHPUnit_Framework_TestSuite
     */
    protected $suite = null;

    /**
     * @var null|\Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher = null;

    protected $tests = array();
    protected $debug = false;
    protected $path = '';
    protected $testcaseClass = 'Codeception\TestCase';
    protected $printer = null;

    protected $settings = array();

    public function __construct(EventDispatcher $dispatcher, $name, $settings)
    {
        $this->settings = $settings;
        $this->dispatcher = $dispatcher;
        $this->suite = $this->createSuite($name);
        $this->path = $settings['path'];

        if ($settings['bootstrap']) $this->settings['bootstrap'] = $this->path . $settings['bootstrap'];
        if (!file_exists($settings['path'] . $settings['class_name'].'.php')) {
            throw new Exception\Configuration($settings['class_name'] . " class doesn't exists in suite folder.\nRun the 'build' command to generate it");
        }

        require_once $settings['path'] . $settings['class_name'].'.php';
        
        $this->initializeModules($settings);
    }

    protected function initializeModules($settings)
    {
        self::$modules = Configuration::modules($settings);
        self::$actions = Configuration::actions(self::$modules);

        foreach (self::$modules as $module) {
            $module->_initialize();
        }
    }

    protected function createSuite($name) {
        $suiteClass = $this->settings['suite_class'];
        if (!class_exists($suiteClass)) throw new \Codeception\Exception\Configuration("Suite class not found");
        $suite = new $suiteClass;
        $suite->setName($name);
        if (!($suite instanceof \PHPUnit_Framework_TestSuite)) throw new \Codeception\Exception\Configuration("Suite class is not inherited from PHPUnit_Framework_TestSuite");
        return $suite;
    }

    public function addTest($path) {

        $testClasses = $this->getClassesFromFile($path);

        foreach ($testClasses as $testClass) {
            $reflected = new \ReflectionClass($testClass);
            if ($reflected->isAbstract()) continue;
            foreach ($reflected->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $test = $this->createTestFromPhpUnitMethod($reflected, $method);
                if (!$test) continue;

                $groups = \PHPUnit_Util_Test::getGroups($testClass, $method->name);
                $this->suite->addTest($test, $groups);
            }
        }
    }

    public function addCept($file)
    {
        $name = $this->relativeName($file);
   	    $this->tests[$name] = $file;

        $cept = new TestCase\Cept($this->dispatcher, array(
            'name' => $name,
            'file' => $file,
            'bootstrap' => $this->settings['bootstrap']
        ));

        $cept->preload();
   	    $this->suite->addTest($cept, $cept->getScenario()->getGroups());
    }

    public function addCest($file) {
        $name = $this->relativeName($file);
   	    $this->tests[$name] = $file;

        $testClasses = $this->getClassesFromFile($file);

        foreach ($testClasses as $testClass) {
            $unit = new $testClass;
            $reflected = new \ReflectionClass($testClass);
            $cestSuite = new \PHPUnit_Framework_TestSuite($testClass.' ');
            $methods = $reflected->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                if ($method->isConstructor()) return;
                if ($method->isDestructor()) return;

                $test = $this->createTestFromCestMethod($unit, $method->name, $file);

                if (!$test) continue;
                $groups = \PHPUnit_Util_Test::getGroups($testClass, $method->name);
                $cestSuite->addTest($test, $groups);
            }
            $this->suite->addTestSuite($cestSuite);
        }
    }

    protected function relativeName($file)
    {
        return $name = str_replace($this->path, '', $file);
    }

    public function run(\Codeception\PHPUnit\Runner $runner, \PHPUnit_Framework_TestResult $result, $options) {

        $this->dispatcher->dispatch('suite.before', new Event\Suite($this->suite, $result, $this->settings));
        $runner->doEnhancedRun($this->suite, $result, $options);
        $this->dispatcher->dispatch('suite.after', new Event\Suite($this->suite, $result, $this->settings));
    }

    public function loadTest($path) {
        if (!file_exists($path)) throw new \Exception("File $path not found");
        if (strrpos(strrev($path), strrev('Cept.php')) === 0) return $this->addCept($path);
        if (strrpos(strrev($path), strrev('Cest.php')) === 0) return $this->addCest($path);
        if (strrpos(strrev($path), strrev('Test.php')) === 0) return $this->addTest($path);
        if (is_dir($path)) {
            $this->path = $path;
            $this->loadTests();
            return;
        }
        throw new \Exception('Test format not supported. Please, check you use the right suffix. Available filetypes: Cept, Cest, Test');
    }

    public function loadTests()
    {
        $finder = Finder::create()->files()->sortByName()->in($this->path);
        if (!empty($this->settings['includes'])) $finder->append($this->settings['includes']);
        $ceptFinder = clone($finder);
        $testFiles = $ceptFinder->name('*Cept.php');
        foreach ($testFiles as $test) {
            $this->addCept($test->getPathname());
        }

        $cestFinder = clone($finder);
        $testFiles = $cestFinder->name('*Cest.php');
        foreach ($testFiles as $test) {
            $this->addCest($test->getPathname());
        }

        // PHPUnit tests
        $testFinder = clone($finder);
        $testFiles = $testFinder->name('*Test.php');
        foreach ($testFiles as $test) {
            $this->addTest($test->getPathname());
        }
    }

    protected function getClassesFromFile($file)
    {
        $loaded_classes = get_declared_classes();
        require_once $file;
        $extra_loaded_classes = get_declared_classes();
        return array_diff($extra_loaded_classes,$loaded_classes);
    }

    protected function createTestFromPhpUnitMethod(\ReflectionClass $class, \ReflectionMethod $method)
    {
        if (!\PHPUnit_Framework_TestSuite::isTestMethod($method) and (strpos($method->name,'should')!==0)) return;
        $test = \PHPUnit_Framework_TestSuite::createTest($class, $method->name);

        if ($test instanceof TestCase\Test) {
            $test->setBootstrap($this->settings['bootstrap']);
            $test->setDispatcher($this->dispatcher);
            $test->setGuyClass($this->settings['class_name']);

            $groups = \PHPUnit_Util_Test::getGroups($class->name, $method->name);
            $test->getScenario()->groups($groups);
        } else {
            if ($this->settings['bootstrap']) require_once $this->settings['bootstrap'];
        }
        return $test;
    }

    protected function createTestFromCestMethod($cestInstance, $methodName, $file)
    {
        $testClass = get_class($cestInstance);
        if (strpos($methodName, '_') === 0) return;

        $target = $testClass.'::'.$methodName;
        $cest = new TestCase\Cest($this->dispatcher, array(
            'name' => $target,
            'instance' => $cestInstance,
            'method' => $methodName,
            'file' => $file,
            'bootstrap' => $this->settings['bootstrap'],
            'guy' => $this->settings['class_name']
        ));

        $cest->setDependencies(\PHPUnit_Util_Test::getDependencies($testClass, $methodName));
        $cest->preload();
        return $cest;
    }

    /**
     * @return null|\PHPUnit_Framework_TestSuite
     */
    public function getSuite() {
        return $this->suite;
    }
}