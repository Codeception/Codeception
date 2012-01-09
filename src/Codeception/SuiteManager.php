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
        $this->suite->addTestFile($path);
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

        $loaded_classes = get_declared_classes();
        require_once $file;
        $extra_loaded_classes = get_declared_classes();

        $testClasses = array_diff($extra_loaded_classes,$loaded_classes);

        foreach ($testClasses as $testClass) {
            $unit = new $testClass;

            $reflected = new \ReflectionClass($testClass);

            $methods = $reflected->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if ($method->isConstructor()) continue;
                if ($method->isDestructor()) continue;

                if (isset($unit->class)) {
                    $target = $unit->class;
                    $target .= $method->isStatic() ? '::'.$method->name : '.'.$method->name;
                } else {
                    $target = $method->name;
                }

                $this->suite->addTest(new \Codeception\TestCase\Cest($this->dispatcher, array(
                    'name' => $name.':'.$target,
                    'class' => $unit,
                    'method' => $method->name,
                    'static' => $method->isStatic(),
                    'signature' => $target,
                    'file' => $file,
           	        'bootstrap' => $this->settings['bootstrap']
                )));
            }
        }
    }

    protected function relativeName($file)
    {
        return $name = str_replace($this->path, '', $file);
    }

    
    public function run(\PHPUnit_Framework_TestResult $result, $options) {
        $runner = new \Codeception\PHPUnit\Runner();
        $runner->setPrinter(new \Codeception\PHPUnit\ResultPrinter\UI($this->dispatcher, $options));

        $this->dispatcher->dispatch('suite.before', new Event\Suite($this->suite));
        $runner->doEnhancedRun($this->suite, $result, array_merge(array('convertErrorsToExceptions' => true), $options));
        $this->dispatcher->dispatch('suite.after', new Event\Suite($this->suite));

        return $runner;
    }
    public function loadTest($path) {
        if (!file_exists($path)) throw new \Exception("File $path not found");
        if (strrpos(strrev($path), strrev('Cept.php')) === 0) return $this->addCept($path);
        if (strrpos(strrev($path), strrev('Spec.php')) === 0) return $this->addCept($path);
        if (strrpos(strrev($path), strrev('Cest.php')) === 0) return $this->addCest($path);
        if (strrpos(strrev($path), strrev('Test.php')) === 0) return $this->addTest($path);
        throw new Exception('Test format not supported. Please, check you use the right suffix. Available filetypes: Cept (Spec), Cest, Test');
    }

    public function loadTests()
    {
        $testFiles = \Symfony\Component\Finder\Finder::create()->files()->name('*Cept.php')->depth('>= 0')->in($this->path);
        foreach ($testFiles as $test) {
            $this->addCept($test->getPathname());
        }
        // old-style namings, right?
        $testFiles = \Symfony\Component\Finder\Finder::create()->files()->name('*Spec.php')->depth('>= 0')->in($this->path);
        foreach ($testFiles as $test) {
            $this->addCept($test->getPathname());
        }

        // tests inside classes
        $testFiles = \Symfony\Component\Finder\Finder::create()->files()->name('*Cest.php')->depth('>= 0')->in($this->path);
        foreach ($testFiles as $test) {
            $this->addCest($test->getPathname());
        }

        // PHPUnit tests
        $testFiles = \Symfony\Component\Finder\Finder::create()->files()->name('*Test.php')->depth('>= 0')->in($this->path);
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


}
