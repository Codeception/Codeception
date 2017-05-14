<?php

namespace Codeception\Template;

use Codeception\InitTemplate;
use Codeception\Util\Template;
use Symfony\Component\Yaml\Yaml;

class Unit extends InitTemplate
{
    protected $configTemplate = <<<EOF
paths:
    tests: {{dir}}
    output: {{dir}}/_output
    support: {{dir}}/_support
    data: {{dir}}
suites:
    unit:
        path: .
{{tester}}        
EOF;

    protected $testerAndModules = <<<EOF
        class_name: UnitTester
        modules:
            enabled:
                # add more modules here
                - Asserts
EOF;


    public function setup()
    {
        $this->sayInfo('This will install Codeception for unit testing only');
        $this->say();
        $dir = $this->ask("Where tests will be stored?", 'tests');

        if (!$this->namespace) {
            $this->namespace = $this->ask("Enter a default namespace for tests (or skip this step)");
        }

        $this->say("Codeception provides additional features for integration tests");
        $this->say("You can access DI containers, ORM, Database.");
        $haveTester = $this->ask("Do you wish to enable them?", false);

        $this->createEmptyDirectory($outputDir = $dir . DIRECTORY_SEPARATOR . '_output');
        $this->createEmptyDirectory($supportDir = $dir . DIRECTORY_SEPARATOR . '_support');

        $configFile = (new Template($this->configTemplate))
            ->place('dir', $dir)
            ->place('tester', $haveTester ? $this->testerAndModules : '')
            ->produce();

        if ($this->namespace) {
            $namespace = rtrim($this->namespace, '\\');
            $configFile = "namespace: $namespace\n" . $configFile;
        }

        $this->createFile('codeception.yml', $configFile);

        if ($haveTester) {
            $this->createHelper('Unit', $supportDir);
            $this->createActor('Unit', $supportDir, Yaml::parse($configFile)['suites']['unit']);
        }

        $this->gitIgnore($outputDir);
        $this->sayInfo("Created test directory inside at $dir");

        $this->say();
        $this->saySuccess("INSTALLATION COMPLETE");

        if ($haveTester) {
            $this->say('Enable more module in codeception.yml');
            $this->say('Access their methods via <bold>$this->tester</bold> object inside Codeception\Test\Unit');
        }

        $this->say();
        $this->say("<bold>Next steps:</bold>");
        $this->say("Create the first test using <comment>codecept g:test unit MyTest</comment>");
        $this->say("Run tests with <comment>codecept run</comment>");
        $this->say("<bold>Happy testing!</bold>");

    }
}