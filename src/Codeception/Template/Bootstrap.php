<?php

namespace Codeception\Template;

use Codeception\InitTemplate;
use Symfony\Component\Yaml\Yaml;

class Bootstrap extends InitTemplate
{
    // defaults
    protected $supportDir = 'tests/_support';
    protected $outputDir = 'tests/_output';
    protected $dataDir = 'tests/_data';
    protected $envsDir = 'tests/_envs';

    public function setup()
    {
        $this->checkInstalled($this->workDir);

        $input = $this->input;
        if ($input->getOption('namespace')) {
            $this->namespace = trim($input->getOption('namespace'), '\\') . '\\';
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

        $this->createUnitSuite();
        $this->say("tests/unit created                 <- unit tests");
        $this->say("tests/unit.suite.yml written       <- unit tests suite configuration");
        $this->createFunctionalSuite();
        $this->say("tests/functional created           <- functional tests");
        $this->say("tests/functional.suite.yml written <- functional tests suite configuration");
        $this->createAcceptanceSuite();
        $this->say("tests/acceptance created           <- acceptance tests");
        $this->say("tests/acceptance.suite.yml written <- acceptance tests suite configuration");

        $this->say(" --- ");
        $this->say();
        $this->saySuccess('Codeception is installed for acceptance, functional, and unit testing');
        $this->say();

        $this->say("<bold>Next steps:</bold>");
        $this->say('1. Edit <bold>tests/acceptance.suite.yml</bold> to set url of your application. Change PhpBrowser to WebDriver to enable browser testing');
        $this->say("2. Edit <bold>tests/functional.suite.yml</bold> to enable a framework module. Remove this file if you don't use a framework");
        $this->say("3. Create your first acceptance tests using <comment>codecept g:cest acceptance First</comment>");
        $this->say("4. Write first test in <bold>tests/acceptance/FirstCest.php</bold>");
        $this->say("5. Run tests using: <comment>codecept run</comment>");
    }

    protected function createDirs()
    {
         $this->createDirectoryFor('tests');
         $this->createEmptyDirectory($this->outputDir);
         $this->createEmptyDirectory($this->dataDir);
         $this->createDirectoryFor($this->supportDir . DIRECTORY_SEPARATOR . '_generated');
         $this->createDirectoryFor($this->supportDir . DIRECTORY_SEPARATOR . "Helper");
         $this->gitIgnore('tests/_output');
         $this->gitIgnore('tests/_support/_generated');
    }

    protected function createFunctionalSuite($actor = 'Functional')
    {
        $suiteConfig = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for functional tests
# Emulate web requests and make application process them
# Include one of framework modules (Symfony2, Yii2, Laravel5) to use it
# Remove this suite if you don't use frameworks

actor: $actor{$this->actorSuffix}
modules:
    enabled:
        # add a framework module here
        - \\{$this->namespace}Helper\Functional
EOF;
        $this->createSuite('functional', $actor, $suiteConfig);
    }

    protected function createAcceptanceSuite($actor = 'Acceptance')
    {
        $suiteConfig = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for acceptance tests.
# Perform tests in browser using the WebDriver or PhpBrowser.
# If you need both WebDriver and PHPBrowser tests - create a separate suite.

actor: $actor{$this->actorSuffix}
modules:
    enabled:
        - PhpBrowser:
            url: http://localhost/myapp
        - \\{$this->namespace}Helper\Acceptance
EOF;
        $this->createSuite('acceptance', $actor, $suiteConfig);
    }

    protected function createUnitSuite($actor = 'Unit')
    {
        $suiteConfig = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for unit or integration tests.

actor: $actor{$this->actorSuffix}
modules:
    enabled:
        - Asserts
        - \\{$this->namespace}Helper\Unit
EOF;
        $this->createSuite('unit', $actor, $suiteConfig);
    }

    public function createGlobalConfig()
    {
        $basicConfig = [
            'paths'    => [
                'tests'   => 'tests',
                'output'     => $this->outputDir,
                'data'    => $this->dataDir,
                'support' => $this->supportDir,
                'envs'    => $this->envsDir,
            ],
            'actor_suffix' => 'Tester',
            'extensions' => [
                'enabled' => ['Codeception\Extension\RunFailed']
            ]
        ];

        $str = Yaml::dump($basicConfig, 4);
        if ($this->namespace) {
            $namespace = rtrim($this->namespace, '\\');
            $str = "namespace: $namespace\n" . $str;
        }
        $this->createFile('codeception.yml', $str);
    }


    protected function createSuite($suite, $actor, $config)
    {
        $this->createDirectoryFor("tests/$suite", "$suite.suite.yml");
        $this->createHelper($actor, $this->supportDir);
        $this->createActor($actor . $this->actorSuffix, $this->supportDir, Yaml::parse($config));
        $this->createFile('tests' . DIRECTORY_SEPARATOR . "$suite.suite.yml", $config);
    }
}
