<?php

declare(strict_types=1);

namespace Codeception\Template;

use Codeception\Extension\RunFailed;
use Codeception\InitTemplate;
use Codeception\Module\Asserts;
use Codeception\Module\PhpBrowser;
use Symfony\Component\Yaml\Yaml;

class Bootstrap extends InitTemplate
{
    // defaults
    protected string $supportDir = 'tests/Support';

    protected string $dataDir = 'tests/Support/Data';

    protected string $envsDir = 'tests/_envs';

    protected string $outputDir = 'tests/_output';


    // default since v5
    protected string $namespace = 'Tests';

    protected string $supportNamespace = 'Support';

    public function setup(): void
    {
        $this->checkInstalled($this->workDir);

        $input = $this->input;
        if ($input->getOption('namespace')) {
            $this->namespace = trim((string) $input->getOption('namespace'), '\\');
        }

        if ($input->hasOption('actor') && $input->getOption('actor')) {
            $this->actorSuffix = $input->getOption('actor');
        }

        $this->say(
            "<fg=white;bg=magenta> Bootstrapping Codeception </fg=white;bg=magenta>\n"
        );

        $this->createGlobalConfig();
        $this->say("File codeception.yml created       <- global configuration");

        $this->createDirs();

        if ($input->hasOption('empty') && $input->getOption('empty')) {
            return;
        }

        if (!class_exists(Asserts::class) || !class_exists(PhpBrowser::class)) {
            $this->addModulesToComposer(['PhpBrowser', 'Asserts']);
        }

        $this->createUnitSuite();
        $this->createFunctionalSuite();
        $this->createAcceptanceSuite();

        $this->say(" --- ");
        $this->say();
        $this->saySuccess('Codeception is installed for acceptance, functional, and unit testing');
        $this->say();

        $this->say("<bold>Next steps:</bold>");
        $this->say('1. Edit <bold>tests/Acceptance.suite.yml</bold> to set url of your application. Change PhpBrowser to WebDriver to enable browser testing');
        $this->say("2. Edit <bold>tests/Functional.suite.yml</bold> to enable a framework module. Remove this file if you don't use a framework");
        $this->say("3. Create your first acceptance tests using <comment>codecept g:cest Acceptance First</comment>");
        $this->say("4. Write first test in <bold>tests/Acceptance/FirstCest.php</bold>");
        $this->say("5. Run tests using: <comment>codecept run</comment>");
    }

    protected function createDirs(): void
    {
        $this->createDirectoryFor('tests');
        $this->createDirectoryFor($this->outputDir);
        $this->createEmptyDirectory($this->dataDir);
        $this->createDirectoryFor($this->supportDir . DIRECTORY_SEPARATOR . '_generated');
        $this->createDirectoryFor($this->supportDir . DIRECTORY_SEPARATOR . "Helper");
        $this->gitIgnore($this->outputDir);
        $this->gitIgnore($this->supportDir . DIRECTORY_SEPARATOR . '/_generated');
    }

    protected function createFunctionalSuite(string $actor = 'Functional'): void
    {
        $suiteConfig = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for functional tests
# Emulate web requests and make application process them
# Include one of framework modules (Symfony, Yii2, Laravel, Phalcon5) to use it
# Remove this suite if you don't use frameworks

actor: {$actor}{$this->actorSuffix}
modules:
    enabled:
        # add a framework module here
step_decorators: ~

EOF;
        $this->createSuite('Functional', $actor, $suiteConfig);
        $this->say("tests/Functional/ created          <- functional tests");
        $this->say("tests/Functional.suite.yml written <- functional test suite configuration");
    }

    protected function createAcceptanceSuite(string $actor = 'Acceptance'): void
    {
        $suiteConfig = <<<EOF
# Codeception Acceptance Test Suite Configuration
#
# Perform tests in a browser by either emulating one using PhpBrowser, or in a real browser using WebDriver.
# If you need both WebDriver and PhpBrowser tests, create a separate suite for each.

actor: {$actor}{$this->actorSuffix}
modules:
    enabled:
        - PhpBrowser:
            url: http://localhost/myapp
# Add Codeception\Step\Retry trait to AcceptanceTester to enable retries
step_decorators:
    - Codeception\Step\ConditionalAssertion
    - Codeception\Step\TryTo
    - Codeception\Step\Retry

EOF;
        $this->createSuite('Acceptance', $actor, $suiteConfig);
        $this->say("tests/Acceptance/ created          <- acceptance tests");
        $this->say("tests/Acceptance.suite.yml written <- acceptance test suite configuration");
    }

    protected function createUnitSuite(string $actor = 'Unit'): void
    {
        $suiteConfig = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for unit or integration tests.

actor: {$actor}{$this->actorSuffix}
modules:
    enabled:
        - Asserts
step_decorators: ~

EOF;
        $this->createSuite('Unit', $actor, $suiteConfig);
        $this->say("tests/Unit/ created                <- unit tests");
        $this->say("tests/Unit.suite.yml written       <- unit test suite configuration");
    }

    public function createGlobalConfig(): void
    {
        $basicConfig = [
            'support_namespace' => $this->supportNamespace,
            'paths'    => [
                'tests'   => 'tests',
                'output'  => $this->outputDir,
                'data'    => $this->dataDir,
                'support' => $this->supportDir,
                'envs'    => $this->envsDir,
            ],
            'actor_suffix' => 'Tester',
            'extensions' => [
                'enabled' => [RunFailed::class]
            ]
        ];

        $str = Yaml::dump($basicConfig, 4);
        if ($this->namespace !== '') {
            $namespace = rtrim($this->namespace, '\\');
            $str = "namespace: {$namespace}\n" . $str;
        }
        $this->createFile('codeception.yml', $str);
    }

    protected function createSuite(string $suite, string $actor, string $config): void
    {
        $settings = Yaml::parse($config);
        $settings['support_namespace'] = $this->supportNamespace;
        $this->createDirectoryFor("tests/{$suite}", "{$suite}.suite.yml");
        $this->createActor($actor . $this->actorSuffix, $this->supportDir, $settings);
        $this->createFile('tests' . DIRECTORY_SEPARATOR . "{$suite}.suite.yml", $config);
    }
}
