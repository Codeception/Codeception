<?php

namespace Codeception;

use Codeception\Event\Suite;
use Codeception\Event\SuiteEvent;
use Codeception\Lib\GroupManager;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
    protected $settings;

    public function __construct(EventDispatcher $dispatcher, $name, array $settings)
    {
        $this->settings = $settings;
        $this->dispatcher = $dispatcher;
        $this->suite = $this->createSuite($name);
        $this->path = $settings['path'];
        $this->groupManager = new GroupManager($settings['groups']);

        if (isset($settings['current_environment'])) {
            $this->env = $settings['current_environment'];
        }
        $this->suite = $this->createSuite($name);
    }

    public function initialize()
    {
        $this->initializeModules();
        $this->dispatcher->dispatch(Events::SUITE_INIT, new SuiteEvent($this->suite, null, $this->settings));
        $this->initializeActors();
        ini_set('xdebug.show_exception_trace', 0); // Issue https://github.com/symfony/symfony/issues/7646
    }

    protected function initializeModules()
    {
        self::$modules = Configuration::modules($this->settings);
        self::$actions = Configuration::actions(self::$modules);

        foreach (self::$modules as $module) {
            $module->_initialize();
        }
    }

    protected function initializeActors()
    {
        if (!file_exists($this->path . $this->settings['class_name'] . '.php')) {
            throw new Exception\Configuration($this->settings['class_name'] . " class doesn't exists in suite folder.\nRun the 'build' command to generate it");
        }
        require_once $this->settings['path'] . DIRECTORY_SEPARATOR . $this->settings['class_name'] . '.php';
    }

    public static function hasModule($moduleName)
    {
        return isset(self::$modules[$moduleName]);
    }

    public function loadTests($path = null)
    {
        $testLoader = new TestLoader($this->settings['path']);
        $path
            ? $testLoader->loadTest($path)
            : $testLoader->loadTests();

        $tests = $testLoader->getTests();
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
                if (!$t instanceof TestCase\Interfaces\Configurable) {
                    continue;
                }
                $t->configDispatcher($this->dispatcher);
                $t->configActor($this->getActor());
                $t->configEnv($this->env);
            }
        }

        if ($test instanceof TestCase\Interfaces\ScenarioDriven) {
            if (!$this->isCurrentEnvironment($test->getScenario()->getEnv())) {
                return;
            }
            $test->preload();
        }

        $groups = $this->groupManager->groupsForTest($test);
        $this->suite->addTest($test, $groups);

        if (!empty($groups) && $test instanceof TestCase\Interfaces\ScenarioDriven && null !== $test->getScenario()) {
            $test->getScenario()->group($groups);
        }
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
