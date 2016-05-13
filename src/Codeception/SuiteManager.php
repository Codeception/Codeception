<?php

namespace Codeception;

use Codeception\Event\Suite;
use Codeception\Event\SuiteEvent;
use Codeception\Exception\ConfigurationException;
use Codeception\Exception\TestRuntimeException;
use Codeception\Lib\Di;
use Codeception\Lib\GroupManager;
use Codeception\Lib\ModuleContainer;
use Codeception\Lib\Notification;
use Codeception\Lib\TestLoader;
use Codeception\TestCase\Interfaces\ScenarioDriven;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SuiteManager
{
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

    /**
     * @var ModuleContainer
     */
    protected $moduleContainer;

    /**
     * @var Di
     */
    protected $di;

    protected $tests = [];
    protected $debug = false;
    protected $path = '';
    protected $printer = null;

    protected $env = null;
    protected $settings;

    public function __construct(EventDispatcher $dispatcher, $name, array $settings)
    {
        $this->settings = $settings;
        $this->dispatcher = $dispatcher;
        $this->di = new Di();
        $this->path = $settings['path'];
        $this->groupManager = new GroupManager($settings['groups']);
        $this->moduleContainer = new ModuleContainer($this->di, $settings);

        $modules = Configuration::modules($this->settings);
        foreach ($modules as $moduleName) {
            $this->moduleContainer->create($moduleName);
        }
        $this->moduleContainer->validateConflicts();
        if (isset($settings['current_environment'])) {
            $this->env = $settings['current_environment'];
        }
        $this->suite = $this->createSuite($name);
    }

    public function initialize()
    {
        $this->dispatcher->dispatch(Events::MODULE_INIT, new SuiteEvent($this->suite, null, $this->settings));
        foreach ($this->moduleContainer->all() as $module) {
            $module->_initialize();
        }
        if (!file_exists(Configuration::supportDir() . $this->settings['class_name'] . '.php')) {
            throw new Exception\ConfigurationException(
                $this->settings['class_name']
                . " class doesn't exist in suite folder.\nRun the 'build' command to generate it"
            );
        }
        $this->dispatcher->dispatch(Events::SUITE_INIT, new SuiteEvent($this->suite, null, $this->settings));
        ini_set('xdebug.show_exception_trace', 0); // Issue https://github.com/symfony/symfony/issues/7646
    }

    public function loadTests($path = null)
    {
        $testLoader = new TestLoader($this->settings['path']);
        $path
            ? $testLoader->loadTest($path)
            : $testLoader->loadTests();

        $tests = $testLoader->getTests();
        if ($this->settings['shuffle']) {
            shuffle($tests);
        }
        foreach ($tests as $test) {
            $this->addToSuite($test);
        }
    }

    protected function addToSuite($test)
    {
        $this->configureTest($test);

        if ($test instanceof \PHPUnit_Framework_TestSuite_DataProvider) {
            foreach ($test->tests() as $t) {
                $this->configureTest($t);
            }
        }

        if ($test instanceof ScenarioDriven) {
            $test->preload();
        }
        if ($test instanceof TestCase) {
            $this->checkEnvironmentExists($test);
            if (!$this->isExecutedInCurrentEnvironment($test)) {
                return; // skip tests from other environments
            }
        }

        $groups = $this->groupManager->groupsForTest($test);
        $this->suite->addTest($test, $groups);

        if (!empty($groups) && $test instanceof TestCase) {
            $test->getScenario()->group($groups);
        }
    }

    protected function createSuite($name)
    {
        $suite = new Lib\Suite();
        $suite->setBaseName(preg_replace('~\s.+$~', '', $name)); // replace everything after space (env name)
        if ($this->settings['namespace']) {
            $name = $this->settings['namespace'] . ".$name";
        }
        $suite->setName($name);
        $suite->setModules($this->moduleContainer->all());
        return $suite;
    }


    public function run(PHPUnit\Runner $runner, \PHPUnit_Framework_TestResult $result, $options)
    {
        $runner->prepareSuite($this->suite, $options);
        $this->dispatcher->dispatch(Events::SUITE_BEFORE, new Event\SuiteEvent($this->suite, $result, $this->settings));
        $runner->doEnhancedRun($this->suite, $result, $options);
        $this->dispatcher->dispatch(Events::SUITE_AFTER, new Event\SuiteEvent($this->suite, $result, $this->settings));
    }


    /**
     * @return \Codeception\Lib\Suite
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * @return ModuleContainer
     */
    public function getModuleContainer()
    {
        return $this->moduleContainer;
    }

    protected function getActor()
    {
        return $this->settings['namespace']
            ? rtrim($this->settings['namespace'], '\\') . '\\' . $this->settings['class_name']
            : $this->settings['class_name'];
    }

    protected function checkEnvironmentExists(\Codeception\TestCase $test)
    {
        $envs = $test->getEnvironment();
        if (empty($envs)) {
            return;
        }
        if (!isset($this->settings['env'])) {
            Notification::warning("Environments are not configured", TestCase::getTestFullName($test));
            return;
        }
        $availableEnvironments = array_keys($this->settings['env']);
        $listedEnvironments = explode(',', implode(',', $test->getEnvironment()));
        foreach ($listedEnvironments as $env) {
            if (!in_array($env, $availableEnvironments)) {
                Notification::warning(
                    "Environment $env was not configured but used in test",
                    TestCase::getTestFullName($test)
                );
            }
        }
    }

    protected function isExecutedInCurrentEnvironment(\Codeception\TestCase $test)
    {
        $envs = $test->getEnvironment();
        if (empty($envs)) {
            return true;
        }
        $currentEnvironments = explode(',', $this->env);
        foreach ($envs as $envList) {
            $envList = explode(',', $envList);
            if (count($envList) == count(array_intersect($currentEnvironments, $envList))) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $t
     * @throws Exception\InjectionException
     */
    protected function configureTest($t)
    {
        if (!$t instanceof TestCase\Interfaces\Configurable) {
            return;
        }
        $t->configDispatcher($this->dispatcher);
        $t->configActor($this->getActor());
        $t->configEnv($this->env);
        $t->configModules($this->moduleContainer);
        $t->configDi($this->di);
        $t->initConfig();
        $this->di->injectDependencies($t);
    }
}
