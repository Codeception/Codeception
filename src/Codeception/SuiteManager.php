<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Lib\Di;
use Codeception\Lib\GroupManager;
use Codeception\Lib\ModuleContainer;
use Codeception\Lib\Notification;
use Codeception\Test\Descriptor;
use Codeception\Test\Filter;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\Test\Loader;
use Codeception\Test\Test;
use Codeception\Test\TestCaseWrapper;
use Codeception\Test\Unit;
use PHPUnit\Runner\Version as PHPUnitVersion;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SuiteManager
{
    protected ?Suite $suite = null;

    protected ?EventDispatcher $dispatcher = null;

    protected GroupManager $groupManager;

    protected ModuleContainer $moduleContainer;

    protected Di $di;

    protected string $env = '';

    protected array $settings = [];

    private Filter $testFilter;

    public function __construct(EventDispatcher $dispatcher, string $name, array $settings, array $options)
    {
        $this->settings = $settings;
        $this->dispatcher = $dispatcher;
        $this->di = new Di();
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

        $this->testFilter = new Filter(
            $options['groups'] ?? null,
            $options['excludeGroups'] ?? null,
            $options['filter'] ?? null,
        );

        $this->suite = $this->createSuite($name);
    }

    public function initialize(): void
    {
        $this->dispatcher->dispatch(new Event\SuiteEvent($this->suite, $this->settings), Events::MODULE_INIT);
        foreach ($this->moduleContainer->all() as $module) {
            $module->_initialize();
        }
        if ($this->settings['actor'] && !file_exists(Configuration::supportDir() . $this->settings['actor'] . '.php')) {
            throw new Exception\ConfigurationException(
                $this->settings['actor']
                . " class doesn't exist in suite folder.\nRun the 'build' command to generate it"
            );
        }
        $this->dispatcher->dispatch(new Event\SuiteEvent($this->suite, $this->settings), Events::SUITE_INIT);
        ini_set('xdebug.show_exception_trace', '0'); // Issue https://github.com/symfony/symfony/issues/7646
    }

    public function loadTests(string $path = null): void
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

    protected function addToSuite(Test $test): void
    {
        if (!$this->testFilter->isNameAccepted($test)) {
            return;
        }

        $this->configureTest($test);

        $this->checkEnvironmentExists($test);
        if (!$this->isExecutedInCurrentEnvironment($test)) {
            return; // skip tests from other environments
        }

        $groups = $this->groupManager->groupsForTest($test);

        if (!$this->testFilter->isGroupAccepted($test, $groups)) {
            return;
        }

        $this->suite->addTest($test);

        if (!empty($groups) && $test instanceof TestInterface) {
            $test->getMetadata()->setGroups($groups);
        }
    }

    protected function createSuite(string $name): Suite
    {
        if ($this->settings['namespace']) {
            $name = $this->settings['namespace'] . '.' . $name;
        }

        $suite = new Suite($this->dispatcher, $name);
        $suite->setBaseName(preg_replace('#\s.+$#', '', $name)); // replace everything after space (env name)
        $suite->setModules($this->moduleContainer->all());

        $suite->reportUselessTests((bool)($this->settings['report_useless_tests'] ?? false));
        $suite->backupGlobals((bool)($this->settings['backup_globals'] ?? false));
        $suite->beStrictAboutChangesToGlobalState((bool)($this->settings['be_strict_about_changes_to_global_state'] ?? false));
        $suite->disallowTestOutput((bool)($this->settings['disallow_test_output'] ?? false));

        if (PHPUnitVersion::series() >= 10) {
            $suite->initPHPUnitConfiguration();
        }
        return $suite;
    }

    public function run(ResultAggregator $resultAggregator): void
    {
        $this->dispatcher->dispatch(new Event\SuiteEvent($this->suite, $this->settings), Events::SUITE_BEFORE);
        try {
            unset($GLOBALS['app']); // hook for not to serialize globals
            $this->suite->run($resultAggregator);
        } finally {
            $this->dispatcher->dispatch(new Event\SuiteEvent($this->suite, $this->settings), Events::SUITE_AFTER);
        }
    }
    public function getSuite(): Suite
    {
        return $this->suite;
    }

    public function getModuleContainer(): ModuleContainer
    {
        return $this->moduleContainer;
    }

    protected function getActor(): ?string
    {
        if (!$this->settings['actor']) {
            return null;
        }

        $namespace = "";

        if ($this->settings['namespace']) {
            $namespace .= '\\' . $this->settings['namespace'];
        }

        if (isset($this->settings['support_namespace'])) {
            $namespace .= '\\' . $this->settings['support_namespace'];
        }
        $namespace = rtrim($namespace, '\\') . '\\';

        return $namespace . $this->settings['actor'];
    }

    protected function checkEnvironmentExists(TestInterface $test): void
    {
        $envs = $test->getMetadata()->getEnv();
        if (empty($envs)) {
            return;
        }
        if (!isset($this->settings['env'])) {
            Notification::warning("Environments are not configured", Descriptor::getTestFullName($test));
            return;
        }
        $listedEnvironments = explode(',', implode(',', $envs));
        foreach ($listedEnvironments as $env) {
            if (!array_key_exists($env, $this->settings['env'])) {
                Notification::warning("Environment {$env} was not configured but used in test", Descriptor::getTestFullName($test));
            }
        }
    }

    protected function isExecutedInCurrentEnvironment(TestInterface $test): bool
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

    protected function configureTest(TestInterface $test): void
    {
        $test->getMetadata()->setServices([
            'di' => clone($this->di),
            'dispatcher' => $this->dispatcher,
            'modules' => $this->moduleContainer
        ]);
        $test->getMetadata()->setCurrent([
            'actor' => $this->getActor(),
            'env' => $this->env,
            'modules' => $this->moduleContainer->all()
        ]);
        if ($test instanceof TestCaseWrapper) {
            $testCase = $test->getTestCase();
            if ($testCase instanceof Unit) {
                $this->configureTest($testCase);
            }
        }
        if ($test instanceof ScenarioDriven) {
            $test->preload();
        }
    }
}
