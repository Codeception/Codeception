<?php

namespace Codeception;


class SuiteManager {

    public static $modules = array();
    public static $methods = array();
    public static $modulesInitialized = false;
	public static $output;

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
            }
        }
        self::initializeModules();
    }

    public function addTest($name, $testPath = null)
   	{
   	    $this->tests[$name] = $testPath;

   	    $this->suite->addTest(new \Codeception\TestCase(array(
   			'name' => $name,
            'file' => $testPath,
            'debug' => $this->debug,
   	        'bootstrap' => $this->bootstrap
        )));
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
        $module = new $modulename;
        self::$modules[$modulename] = $module;
        return $module;
    }

    public static function removeModule($dumpModule) {
        foreach (self::$modules as $name => $module) {
            if (get_class($dumpModule) == $name) {
                unset(self::$modules[$name]);
                return;
            }
        }
    }

    public static function initializeModules() {
        foreach (self::$modules as $modulename => $module) {
            $module->_initialize();
            $class = new \ReflectionClass($modulename);
            $methods = $class->getMethods();
            foreach ($methods as $method) {
			    if (strpos($method->name,'_')===0) continue;
			    if (!$method->isPublic()) continue;
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

    public function loadTest($name, $path) {
        $testFiles = \Symfony\Component\Finder\Finder::create()->files()->name($name.'Spec.php')->in($path);
        foreach ($testFiles as $test) {
            $this->addTest(basename($test), $test);
            break;
        }
    }

    public function loadTests($path)
    {
        $testFiles = \Symfony\Component\Finder\Finder::create()->files()->name('*Spec.php')->in($path);
        foreach ($testFiles as $test) {
            $this->addTest(basename($test), $test);
        }
    }


}
