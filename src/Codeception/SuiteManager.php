<?php
namespace Codeception;

use Codeception\Lib\Di;
use Codeception\Lib\GroupManager;
use Codeception\Lib\ModuleContainer;
use Codeception\Lib\Notification;
use Codeception\Test\Interfaces\Configurable;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\Test\Loader;
use Codeception\Test\Descriptor;
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
     * @var Loader
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
        $this->dispatcher->dispatch(Events::MODULE_INIT, new Event\SuiteEvent($this->suite, null, $this->settings));
        foreach ($this->moduleContainer->all() as $module) {
            $module->_initialize();
        }
        if (!file_exists(Configuration::supportDir() . $this->settings['class_name'] . '.php')) {
            throw new Exception\ConfigurationException(
                $this->settings['class_name']
                . " class doesn't exist in suite folder.\nRun the 'build' command to generate it"
            );
        }
        $this->dispatcher->dispatch(Events::SUITE_INIT, new Event\SuiteEvent($this->suite, null, $this->settings));
        ini_set('xdebug.show_exception_trace', 0); // Issue https://github.com/symfony/symfony/issues/7646
    }

    public function loadTests($path = null)
    {
        $testLoader = new Loader($this->settings);
        $testLoader->loadTests($path);

        $tests = $testLoader->getTests();
        if ($this->settings['shuffle']) {
            shuffle($tests);
        }
        foreach ($tests as $test) {
            $this->addToSuite($test);
        }
        $this->suite->reorderDependencies();
    }

    protected function addToSuite($test)
    {
        $this->configureTest($test);

        if ($test instanceof \PHPUnit_Framework_TestSuite_DataProvider) {
            foreach ($test->tests() as $t) {
                $this->configureTest($t);
            }
        }
        if ($test instanceof TestInterface) {
            $this->checkEnvironmentExists($test);
            if (!$this->isExecutedInCurrentEnvironment($test)) {
                return; // skip tests from other environments
            }
        }

        $groups = $this->groupManager->groupsForTest($test);

        // registering group for data providers
        if ($test instanceof \PHPUnit_Framework_TestSuite_DataProvider) {
            $groupDetails = [];
            foreach ($groups as $group) {
                $groupDetails[$group] = $test->getGroupDetails()['default'];
            }
            $test->setGroupDetails($groupDetails);
        }

        $this->suite->addTest($test, $groups);

        if (!empty($groups) && $test instanceof TestInterface) {
            $test->getMetadata()->setGroups($groups);
        }
    }

    protected function createSuite($name)
    {
        $suite = new Suite();
        $suite->setBaseName(preg_replace('~\s.+$~', '', $name)); // replace everything after space (env name)
        if ($this->settings['namespace']) {
            $name = $this->settings['namespace'] . ".$name";
        }
        $suite->setName($name);
        if (isset($this->settings['backup_globals'])) {
            $suite->setBackupGlobals((bool) $this->settings['backup_globals']);
        }
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
     * @return \Codeception\Suite
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

    protected function checkEnvironmentExists(TestInterface $test)
    {
        $envs = $test->getMetadata()->getEnv();
        if (empty($envs)) {
            return;
        }
        if (!isset($this->settings['env'])) {
            Notification::warning("Environments are not configured", Descriptor::getTestFullName($test));
            return;
        }
        $availableEnvironments = array_keys($this->settings['env']);
        $listedEnvironments = explode(',', implode(',', $envs));
        foreach ($listedEnvironments as $env) {
            if (!in_array($env, $availableEnvironments)) {
                Notification::warning("Environment $env was not configured but used in test", Descriptor::getTestFullName($test));
            }
        }
    }

    protected function isExecutedInCurrentEnvironment(TestInterface $test)
    {
        $envs = $test->getMetadata()->getEnv();
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
        if (!$t instanceof TestInterface) {
            return;
        }
        $t->getMetadata()->setServices([
            'di' => clone($this->di),
            'dispatcher' => $this->dispatcher,
            'modules' => $this->moduleContainer
        ]);
        $t->getMetadata()->setCurrent([
            'actor' => $this->getActor(),
            'env' => $this->env,
            'modules' => $this->moduleContainer->all()
        ]);
        if ($t instanceof ScenarioDriven) {
            $t->preload();
        }
    }
}
