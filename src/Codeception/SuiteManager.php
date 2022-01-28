<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Lib\Di;
use Codeception\Lib\GroupManager;
use Codeception\Lib\ModuleContainer;
use Codeception\Lib\Notification;
use Codeception\PHPUnit\FilterTest;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\Test\Loader;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\Test as PHPUnitTest;
use PHPUnit\Framework\TestResult;
use PHPUnit\Runner\Filter\ExcludeGroupFilterIterator;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\Runner\Filter\IncludeGroupFilterIterator;
use ReflectionProperty;
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

    public function __construct(EventDispatcher $dispatcher, string $name, array $settings)
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
        $this->suite = $this->createSuite($name);
    }

    public function initialize(): void
    {
        $this->dispatcher->dispatch(new Event\SuiteEvent($this->suite, null, $this->settings), Events::MODULE_INIT);
        foreach ($this->moduleContainer->all() as $module) {
            $module->_initialize();
        }
        if ($this->settings['actor'] && !file_exists(Configuration::supportDir() . $this->settings['actor'] . '.php')) {
            throw new Exception\ConfigurationException(
                $this->settings['actor']
                . " class doesn't exist in suite folder.\nRun the 'build' command to generate it"
            );
        }
        $this->dispatcher->dispatch(new Event\SuiteEvent($this->suite, null, $this->settings), Events::SUITE_INIT);
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

    protected function addToSuite(PHPUnitTest $test): void
    {
        if ($test instanceof TestInterface) {
            $this->configureTest($test);
        }

        if ($test instanceof DataProviderTestSuite) {
            foreach ($test->tests() as $t) {
                $this->addToSuite($t);
            }
            return;
        }

        if ($test instanceof TestInterface) {
            $this->checkEnvironmentExists($test);
            if (!$this->isExecutedInCurrentEnvironment($test)) {
                return; // skip tests from other environments
            }
        }

        $groups = $this->groupManager->groupsForTest($test);

        $this->suite->addTest($test, $groups);

        if (!empty($groups) && $test instanceof TestInterface) {
            $test->getMetadata()->setGroups($groups);
        }
    }

    protected function createSuite(string $name): Suite
    {
        $suite = new Suite($this->dispatcher);
        $suite->setBaseName(preg_replace('#\s.+$#', '', $name)); // replace everything after space (env name)
        if ($this->settings['namespace']) {
            $name = $this->settings['namespace'] . ".{$name}";
        }
        $suite->setName($name);
        if (isset($this->settings['backup_globals'])) {
            $suite->setBackupGlobals((bool) $this->settings['backup_globals']);
        }

        if (isset($this->settings['be_strict_about_changes_to_global_state']) && method_exists($suite, 'setbeStrictAboutChangesToGlobalState')) {
            $suite->setbeStrictAboutChangesToGlobalState((bool)$this->settings['be_strict_about_changes_to_global_state']);
        }
        $suite->setModules($this->moduleContainer->all());
        return $suite;
    }

    public function run(TestResult $result, array $options): void
    {
        $this->prepareSuite($this->suite, $options);
        $this->dispatcher->dispatch(new Event\SuiteEvent($this->suite, $result, $this->settings), Events::SUITE_BEFORE);
        try {
            unset($GLOBALS['app']); // hook for not to serialize globals
            $this->suite->run($result);
        } finally {
            $this->dispatcher->dispatch(new Event\SuiteEvent($this->suite, $result, $this->settings), Events::SUITE_AFTER);
        }
    }

    public function prepareSuite(PHPUnitTest $suite, array $options): void
    {
        $filterAdded = false;

        $filterFactory = new Factory();
        if (!empty($options['groups'])) {
            $filterAdded = true;
            $this->addFilterToFactory(
                $filterFactory,
                IncludeGroupFilterIterator::class,
                $options['groups']
            );
        }

        if (!empty($options['excludeGroups'])) {
            $filterAdded = true;
            $this->addFilterToFactory(
                $filterFactory,
                ExcludeGroupFilterIterator::class,
                $options['excludeGroups']
            );
        }

        if (!empty($options['filter'])) {
            $filterAdded = true;
            $this->addFilterToFactory(
                $filterFactory,
                FilterTest::class,
                $options['filter']
            );
        }

        if ($filterAdded) {
            $suite->injectFilter($filterFactory);
        }
    }

    private function addFilterToFactory(Factory $filterFactory, string $filterClass, $filterParameter)
    {
        $filterReflectionClass = new \ReflectionClass($filterClass);

        $property = new ReflectionProperty(get_class($filterFactory), 'filters');
        $property->setAccessible(true);

        $filters = $property->getValue($filterFactory);
        $filters []= [
            $filterReflectionClass,
            $filterParameter,
        ];
        $property->setValue($filterFactory, $filters);
        $property->setAccessible(false);
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
        return $this->settings['namespace']
            ? rtrim($this->settings['namespace'], '\\') . '\\' . $this->settings['actor']
            : $this->settings['actor'];
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
        if ($test instanceof ScenarioDriven) {
            $test->preload();
        }
    }
}
