<?php

namespace Codeception;

use Codeception\Event\Suite;
use Codeception\Event\SuiteEvent;
use Codeception\Lib\GroupManager;
use Codeception\Lib\Parser;
use Codeception\TestLoader;
use Codeception\Util\Annotation;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\Finder;

class SuiteManager
{

    public static $modules = array();
    public static $actions = array();
    public static $environment;
    public static $name;

    /**
     * @var \PHPUnit_Framework_TestSuite
     */
    protected $suite = null;

    /**
     * @var null|\Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher = null;

    /**
     * @var GroupManager
     */
    protected $groupManager;

    /**
     * @var TestLoader
     */
    protected $testLoader;

    protected $tests = array();
    protected $debug = false;
    protected $path = '';
    protected $printer = null;
    protected $env = null;


    protected $settings = array();

    public function __construct(EventDispatcher $dispatcher, $name, $settings)
    {
        $this->settings = $settings;
        $this->dispatcher = $dispatcher;
        $this->suite = $this->createSuite($name);
        $this->path = $settings['path'];
        $this->groupManager = new GroupManager($settings['groups']);
        $this->testLoader = new TestLoader($settings['path']);

        if (isset($settings['current_environment'])) {
            $this->env = $settings['current_environment'];
        }
        $this->suite = $this->createSuite($name);

        if (!file_exists($settings['path'] . $settings['class_name'] . '.php')) {
            throw new Exception\Configuration($settings['class_name'] . " class doesn't exists in suite folder.\nRun the 'build' command to generate it");
        }
        $this->initializeModules($settings);
        $this->dispatcher->dispatch(Events::SUITE_INIT, new SuiteEvent($this->suite, null, $this->settings));
        require_once $this->settings['path'] . DIRECTORY_SEPARATOR . $this->settings['class_name'] . '.php';
    }

    public static function hasModule($moduleName)
    {
        return isset(self::$modules[$moduleName]);
    }

    protected function initializeModules($settings)
    {
        self::$modules = Configuration::modules($settings);
        self::$actions = Configuration::actions(self::$modules);
        
        foreach (self::$modules as $module) {
            $module->_initialize();
        }             
    }

    public function loadTests($path = null)
    {
        $path
            ? $this->testLoader->loadTest($this->settings['path'] . $path)
            : $this->testLoader->loadTests();

        $tests = $this->testLoader->getTests();
        foreach ($tests as $test) {
            $this->addToSuite($test);
        }
    }

    protected function addToSuite($test)
    {
        if ($test instanceof TestCase\Interfaces\Configurable) {
            $test->configDispatcher($this->dispatcher);
            $test->configActor($this->getActor());
            $test->configEnv($this->env);
        }

        if ($test instanceof \PHPUnit_Framework_TestSuite_DataProvider) {
            foreach ($test->tests() as $t) {
                $t->configDispatcher($this->dispatcher);
                $t->configActor($this->getActor());
                $t->configEnv($this->env);
            }
        }

        if ($test instanceof \Codeception\TestCase) {
            if (!$this->isCurrentEnvironment($test->getScenario()->getEnv())) {
                return;
            }
        }
        if ($test instanceof TestCase\Interfaces\ScenarioDriven) {
            $test->preload();
        }

        $groups = $this->groupManager->groupsForTest($test);
        $this->suite->addTest($test, $groups);
    }
    
    protected function createSuite($name)
    {
        $suite = new \PHPUnit_Framework_TestSuite();
        $suite->baseName = $this->env
            ? substr($name, 0, strpos($name, '-' . $this->env))
            : $name;

        if ($this->settings['namespace']) {
            $name = $this->settings['namespace'] . ".$name";
        }
        $suite->setName($name);
        if (!($suite instanceof \PHPUnit_Framework_TestSuite)) {
            throw new Exception\Configuration("Suite class is not inherited from PHPUnit_Framework_TestSuite");
        }
        return $suite;
    }


    public function run(PHPUnit\Runner $runner, \PHPUnit_Framework_TestResult $result, $options)
    {
        $this->dispatcher->dispatch(Events::SUITE_BEFORE, new Event\SuiteEvent($this->suite, $result, $this->settings));
        $runner->doEnhancedRun($this->suite, $result, $options);
        $this->dispatcher->dispatch(Events::SUITE_AFTER, new Event\SuiteEvent($this->suite, $result, $this->settings));
    }


    /**
     * @return null|\PHPUnit_Framework_TestSuite
     */
    public function getSuite()
    {
        return $this->suite;
    }

    protected function isCurrentEnvironment($envs)
    {
        if (empty($envs)) {
            return true;
        }
        return $this->env and in_array($this->env, $envs);
    }

    protected function getActor()
    {
        return $this->settings['namespace']
            ? $this->settings['namespace'] . '\\' . $this->settings['class_name']
            : $this->settings['class_name'];
    }
}
