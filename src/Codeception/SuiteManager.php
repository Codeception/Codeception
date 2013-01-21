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


    protected $defaults = array(
        'class_name' => 'NoGuy',
        'modules' => array('enabled' => array(), 'config' => array()),
        'bootstrap' => false,
        'suite_class' => '\PHPUnit_Framework_TestSuite',
        'colors' => true,
        'memory_limit' => '1024M',
        'path' => ''
    );

    protected $settings = array();

    public function __construct(EventDispatcher $dispatcher, $name, $settings) {
        $this->settings = array_merge($this->defaults, $settings);
        $this->dispatcher = $dispatcher;
        $this->suite = $this->createSuite($name);
        $this->path = $settings['path'];
        $this->settings['bootstrap'] = $this->path . $settings['bootstrap'];

        if (!file_exists($settings['path'] . $settings['class_name'].'.php')) {
            throw new \Codeception\Exception\Configuration($settings['class_name'] . " class doesn't exists in suite folder.\nRun the 'build' command to generate it");
        }
        require_once $settings['path'] . $settings['class_name'].'.php';

        self::$modules = \Codeception\Configuration::modules($settings);
        self::$actions = \Codeception\Configuration::actions(self::$modules);
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
                if (!\PHPUnit_Framework_TestSuite::isTestMethod($method) and (strpos($method->getName(),'should')!==0)) continue;
                $test = \PHPUnit_Framework_TestSuite::createTest($reflected, $method->getName());
                $this->suite->addTest($test);
                if ($test instanceof \Codeception\TestCase\Test) {
                    $test->setBootstrap($this->settings['bootstrap']);
                    $test->setDispatcher($this->dispatcher);
                    $test->setGuyClass($this->settings['class_name']);
                } else {
                    if ($this->settings['bootstrap']) require_once $this->settings['bootstrap'];
                }
            }
        }
    }

    public function addCept($file)
   	{
        $name = $this->relativeName($file);
   	    $this->tests[$name] = $file;

   	    $this->suite->addTest(new \Codeception\TestCase\Cept($this->dispatcher, array(
   			'name' => $name,
            'file' => $file,
   	        'bootstrap' => $this->settings['bootstrap']
        )));
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
                if ($method->isConstructor()) continue;
                if ($method->isDestructor()) continue;

                $target = $method->name;
                if (isset($unit->class)) {
                    $target = $unit->class;
                    $target .= $method->isStatic() ? '::'.$method->name : '.'.$method->name;
                } else {
                    $target = get_class($unit).'::'.$method->name;
                }

                $cestSuite->addTest(new \Codeception\TestCase\Cest($this->dispatcher, array(
                    'name' => $name.'::'.$target,
                    'class' => $unit,
                    'method' => $method->name,
                    'static' => $method->isStatic(),
                    'signature' => $target,
                    'file' => $file,
           	        'bootstrap' => $this->settings['bootstrap'],
                    'guy' => $this->settings['class_name']
                )));
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
        throw new \Exception('Test format not supported. Please, check you use the right suffix. Available filetypes: Cept (Spec), Cest, Test');
    }

    public function loadTests()
    {
        $finder = Finder::create()->files()->sortByName()->depth('>= 0')->in($this->path);
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

    /**
     * @return null|\PHPUnit_Framework_TestSuite
     */
    public function getSuite() {
        return $this->suite;
    }

    protected function getClassesFromFile($file)
    {
        $loaded_classes = get_declared_classes();
        require_once $file;
        $extra_loaded_classes = get_declared_classes();
        return array_diff($extra_loaded_classes,$loaded_classes);
    }
}