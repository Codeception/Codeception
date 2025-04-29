<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Command\Shared\ActorTrait;
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
    use ActorTrait;

    protected ?Suite $suite = null;
    protected GroupManager $groupManager;
    protected ModuleContainer $moduleContainer;
    protected Di $di;
    protected string $env = '';
    protected array $settings = [];
    private Filter $testFilter;

    public function __construct(protected ?EventDispatcher $dispatcher, string $name, array $settings, array $options)
    {
        $this->settings       = $settings;
        $this->di             = new Di();
        $this->groupManager   = new GroupManager($settings['groups']);
        $this->moduleContainer = new ModuleContainer($this->di, $settings);

        foreach (Configuration::modules($this->settings) as $moduleName) {
            $this->moduleContainer->create($moduleName);
        }
        $this->moduleContainer->validateConflicts();
        $this->env = $settings['current_environment'] ?? '';

        $this->testFilter = new Filter(
            $options['groups'] ?? null,
            $options['excludeGroups'] ?? null,
            $options['filter'] ?? null,
        );

        $this->suite = $this->createSuite($name);
    }

    public function initialize(): void
    {
        $this->dispatch(Events::MODULE_INIT);
        foreach ($this->moduleContainer->all() as $module) {
            $module->_initialize();
        }

        if ($this->settings['actor'] && !file_exists(Configuration::supportDir() . $this->settings['actor'] . '.php')) {
            throw new Exception\ConfigurationException(
                $this->settings['actor']
                . " class doesn't exist in suite folder.\nRun the 'build' command to generate it"
            );
        }

        $this->dispatch(Events::SUITE_INIT);
        ini_set('xdebug.show_exception_trace', '0'); // See https://github.com/symfony/symfony/issues/7646
    }

    public function loadTests(?string $path = null): void
    {
        $loader = new Loader($this->settings);
        $loader->loadTests($path);

        $tests = $loader->getTests();
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
            return;
        }

        $groups = $this->groupManager->groupsForTest($test);
        if (!$this->testFilter->isGroupAccepted($test, $groups)) {
            return;
        }

        $this->suite->addTest($test);
        if ($groups !== []) {
            $test->getMetadata()->setGroups($groups);
        }
    }

    protected function createSuite(string $name): Suite
    {
        if ($this->settings['namespace']) {
            $name = $this->settings['namespace'] . '.' . $name;
        }

        $suite = new Suite($this->dispatcher, $name);
        $suite->setBaseName(preg_replace('#\s.+$#', '', $name));
        $suite->setModules($this->moduleContainer->all());

        $suite->reportUselessTests(!empty($this->settings['report_useless_tests']));
        $suite->backupGlobals(!empty($this->settings['backup_globals']));
        $suite->beStrictAboutChangesToGlobalState(!empty($this->settings['be_strict_about_changes_to_global_state']));
        $suite->disallowTestOutput(!empty($this->settings['disallow_test_output']));

        if (PHPUnitVersion::series() >= 10) {
            $suite->initPHPUnitConfiguration();
        }

        return $suite;
    }

    public function run(ResultAggregator $resultAggregator): void
    {
        $this->dispatch(Events::SUITE_BEFORE);
        try {
            unset($GLOBALS['app']);
            $this->suite->run($resultAggregator);
        } finally {
            $this->dispatch(Events::SUITE_AFTER);
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

    protected function checkEnvironmentExists(TestInterface $test): void
    {
        $envs = $test->getMetadata()->getEnv();
        if ($envs === [] || !isset($this->settings['env'])) {
            return;
        }
        $missing = array_diff($envs, array_keys($this->settings['env']));
        foreach ($missing as $env) {
            Notification::warning("Environment {$env} was not configured but used in test", Descriptor::getTestFullName($test));
        }
    }

    protected function isExecutedInCurrentEnvironment(TestInterface $test): bool
    {
        $envs = $test->getMetadata()->getEnv();
        if ($envs === []) {
            return true;
        }

        $current = array_filter(array_map('trim', explode(',', $this->env)));
        foreach ($envs as $envList) {
            $envList = array_filter(array_map('trim', explode(',', $envList)));
            if ($envList === [] || array_diff($envList, $current) === []) {
                return true;
            }
        }

        return false;
    }

    protected function configureTest(TestInterface $test): void
    {
        $di = clone $this->di;

        $test->getMetadata()->setServices([
            'di' => $di,
            'dispatcher' => $this->dispatcher,
            'modules' => $this->moduleContainer,
        ]);

        $test->getMetadata()->setCurrent([
            'actor' => $this->getActorClassName(),
            'env' => $this->env,
            'modules' => $this->moduleContainer->all(),
        ]);

        if ($test instanceof TestCaseWrapper) {
            $di->set(new Scenario($test));

            $testCase = $test->getTestCase();
            if ($testCase instanceof Unit) {
                $testCase->setMetadata($test->getMetadata());
            }
        }

        if ($test instanceof ScenarioDriven) {
            $test->preload();
        }
    }

    private function dispatch(string $event): void
    {
        $this->dispatcher->dispatch(new Event\SuiteEvent($this->suite, $this->settings), $event);
    }
}
