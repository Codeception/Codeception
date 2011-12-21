<?php

namespace Codeception;

use Symfony\Component\Finder\Finder;
use \Symfony\Component\EventDispatcher\EventDispatcher;


class SuiteManager {

    public $modules = array();
    public $actions = array();

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
        'class_name' => '',
        'modules' => array('enabled' => array(), 'config' => array()),
        'bootstrap' => false,
        'suite_class' => '\PHPUnit_Framework_TestSuite',
        'colors' => true,
        'memory_limit' => '1024M',
        'path' => ''
    );

    protected $settings = array();

    public function __construct(EventDispatcher $dispatcher, $settings) {
        $this->settings = array_merge_recursive($this->defaults, $settings);
        $this->dispatcher = $dispatcher;
        $this->modules = \Codeception\Configuration::modules($settings);
        $this->actions = \Codeception\Configuration::actions($this->modules);
        $this->suite = $this->createSuite();
        $this->path = $settings['path'];
    }

    protected function createSuite() {
        $suiteClass = $this->settings['suite_class'];
        if (!class_exists($suiteClass)) throw new \Codeception\Exception\Configuration("Suite class not found");
        $suite = new $suiteClass;
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

    protected function relativeName($file)
    {
        return $name = str_replace($this->path, '', $file);
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
                    'file' => $file,
           	        'bootstrap' => $this->settings['bootstrap']
                )));
            }
        }
    }
    
    public function run($result, $options) {

        $listener = new \Codeception\PHPUnit\Listener($this->dispatcher);
        $result->addListener($listener);

        $this->subscribeModules();

        $runner = new \Codeception\Runner();

        $this->dispatcher->dispatch('suite.before', new \Codeception\Event\Suite($this->suite));
        $runner->doEnhancedRun($this->suite, $result, array_merge(array('convertErrorsToExceptions' => true), $options));
        $this->dispatcher->dispatch('suite.after', new \Codeception\Event\Suite($this->suite));

    }

    protected function subscribeModules()
    {
        foreach ($this->modules as $module) {
            $this->dispatcher->addSubscriber($module);
        }
    }

	public function saveTestAsFeature($test, $path) {
		$text = readfile($this->tests[$test]);
	}

    /**
     * @return \PHPUnit_Framework_TestSuite
     */
    public function getCurrentSuite() {
        return $this->suite;
    }

    public function setBootstrtap($bootstrap) {
        $this->bootstrap = $bootstrap;
    }

    public function loadTest($path) {
        if (!file_exists($path)) throw new \Exception("File $path not found");
        if (strrpos(strrev($path), strrev('Cept.php')) === 0) $this->addCept(basename($path), $path);
        if (strrpos(strrev($path), strrev('Cest.php')) === 0) $this->addCest(basename($path), $path);
        if (strrpos(strrev($path), strrev('Test.php')) === 0) $this->addTest($path);
    }

    public function loadTests()
    {
        $testFiles = \Symfony\Component\Finder\Finder::create()->files()->name('*Cept.php')->in($this->path);
        foreach ($testFiles as $test) {
            $this->addCept($test);
        }
        // old-style namings, right?
        $testFiles = \Symfony\Component\Finder\Finder::create()->files()->name('*Spec.php')->in($this->path);
        foreach ($testFiles as $test) {
            $this->addCept($test);
        }

        // tests inside classes
        $testFiles = \Symfony\Component\Finder\Finder::create()->files()->name('*Cest.php')->in($this->path);
        foreach ($testFiles as $test) {
            $this->addCest($test);
        }

        // PHPUnit tests
        $testFiles = \Symfony\Component\Finder\Finder::create()->files()->name('*Test.php')->in($this->path);
        foreach ($testFiles as $test) {
            $this->addTest($test->getPathname());
        }
    }


}
