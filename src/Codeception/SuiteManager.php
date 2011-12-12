<?php

namespace Codeception;

use Symfony\Component\Finder\Finder;


class SuiteManager {

    public static $modules = array();
    public static $methods = array();
    public static $modulesInitialized = false;
	public static $output;

    /**
     * @var \PHPUnit_Framework_TestSuite
     */
	protected $suite = null;
	protected $tests = array();
	protected $debug = false;
    protected $testcaseClass = 'Codeception\TestCase';
    protected $bootstrap = null;
    
    protected static $settings = array(
        'class_name' => '',
        'modules' => array('enabled' => array(), 'config' => array()),
        'bootstrap' => false,
        'suite_class' => '\PHPUnit_Framework_TestSuite',
        'colors' => true,
        'memory_limit' => '1024M'
    );

    public function __construct(\PHPUnit_Framework_TestSuite $suite, $debug = false) {
        $this->suite = new $suite;
        $this->debug = $debug;
    }

    public static function init($settings = array())
    {
        if (!isset($settings['modules'])) throw new \Codeception\Exception\Configuration('No modules configured!');
        self::detachModules();

        $settings = array_merge_recursive(self::$settings, $settings);

        $modules = $settings['modules']['enabled'];
        if (!isset($settings['modules']['config'])) $settings['modules']['config'] = array();
        foreach ($modules as $module_name) {
            $module = self::addModule('\Codeception\Module\\'.$module_name);
            if (isset($settings['modules']['config'][$module_name])) {
                $module->_setConfig($settings['modules']['config'][$module_name]);
            } else {
				if ($module->_hasRequiredFields()) throw new \Codeception\Exception\ModuleConfig($module_name, "Module $module_name is not configured. Please check out it's required fields");
			}
			
        }
        self::initializeModules();
    }

    public function addCept($name, $testPath = null)
   	{
   	    $this->tests[$name] = $testPath;

   	    $this->suite->addTest(new \Codeception\TestCase\Cept('testCodecept', array(
   			'name' => $name,
            'file' => $testPath,
            'debug' => $this->debug,
   	        'bootstrap' => $this->bootstrap
        )));
   	}

    public function addTest($path) {
        $this->suite->addTestFile($path);

    }

    public function addCest($name, $testPath = null) {
        $this->tests[$name] = $testPath;

        $loaded_classes = get_declared_classes();
        require_once $testPath;
        $extra_loaded_classes = get_declared_classes();

        $testClasses = array_diff($extra_loaded_classes,$loaded_classes);

        foreach ($testClasses as $testClass) {
            $unit = new $testClass;

            $reflected = new \ReflectionClass($testClass);

            $methods = $reflected->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if ($method->isConstructor()) continue;
                if ($method->isDestructor()) continue;

                $target = $unit->class;
                $target .= $method->isStatic() ? '::'.$method->name : '.'.$method->name;

                $this->suite->addTest(new \Codeception\TestCase\Cest($target, array(
                    'name' => $target,
                    'class' => $unit,
                    'method' => $method->name,
                    'static' => $method->isStatic(),
                    'file' => $testPath,
                    'debug' => $this->debug,
           	        'bootstrap' => $this->bootstrap
                )));
            }
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

    /**
     * @static
     * @param $modulename
     * @param null $path
     * @return TestGuy_Module
     */
    public static function addModule($modulename, $path = null) {
        if ($path) require_once $path;
        if (!($modulename instanceof \Codeception\Module)) {
            $module = new $modulename;
        } else {
            $module = $modulename;
            $modulename = get_class($module);
        }
        self::$modules[$modulename] = $module;
        return $module;
    }

    public static function removeModule($dumpModule) {
        foreach (self::$modules as $name => $module) {
            if ($dumpModule == $name) {
                unset(self::$modules[$name]);
                return;
            }
        }
    }

    public static function initializeModules() {
        foreach (self::$modules as $modulename => $module) {
            $module->_initialize();
            $class = new \ReflectionClass($modulename);
            $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
			    if (strpos($method->name,'_')===0) continue;
                \Codeception\SuiteManager::$methods[$method->name] = $modulename;
            }
        }
        self::$modulesInitialized = true;
    }

    public static function detachModules()
    {
        self::$modulesInitialized = false;
        self::$modules = array();
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

    public function loadTests($path)
    {
        $testFiles = \Symfony\Component\Finder\Finder::create()->files()->name('*Cept.php')->in($path);
        foreach ($testFiles as $test) {
            $this->addCept(basename($test), $test);
        }
        // old-style namings, right?
        $testFiles = \Symfony\Component\Finder\Finder::create()->files()->name('*Spec.php')->in($path);
        foreach ($testFiles as $test) {
            $this->addCept(basename($test), $test);
        }

        // tests inside classes
        $testFiles = \Symfony\Component\Finder\Finder::create()->files()->name('*Cest.php')->in($path);
        foreach ($testFiles as $test) {
            $this->addCest(basename($test), $test);
        }

        // PHPUnit tests
        $testFiles = \Symfony\Component\Finder\Finder::create()->files()->name('*Test.php')->in($path);
        foreach ($testFiles as $test) {
            $this->addTest($test->getPathname());
        }


    }


}
