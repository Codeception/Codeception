<?php

declare(strict_types=1);

namespace Codeception\Template;

use Codeception\InitTemplate;
use Codeception\Module\Asserts;
use Codeception\Util\Template;
use Symfony\Component\Yaml\Yaml;

class Unit extends InitTemplate
{
    protected string $configTemplate = <<<EOF
suites:
    unit:
        path: .
{{tester}}
settings:
    shuffle: true
    lint: true
paths:
    tests: {{baseDir}}
    output: {{baseDir}}/_output
    support: {{baseDir}}/Support
    data: {{baseDir}}/Support/Data
     
EOF;

    protected string $testerAndModules = <<<EOF
        actor: UnitTester
        modules:
            enabled:
                # add more modules here
                - Asserts
        step_decorators: ~

EOF;

    public function setup(): void
    {
        $this->sayInfo('This will install Codeception for unit testing only');
        $this->say();
        $dir = $this->ask("Where tests will be stored?", 'tests');

        if ($this->namespace === '') {
            $this->namespace = $this->ask("Enter a default namespace for tests (or skip this step)");
        }

        $this->say();
        $this->say("Codeception provides additional features for integration tests");
        $this->say("Like accessing frameworks, ORM, Database.");
        $haveTester = $this->ask("Do you wish to enable them?", false);

        $this->createDirectoryFor($outputDir = $dir . DIRECTORY_SEPARATOR . '_output');
        $this->createDirectoryFor($supportDir = $dir . DIRECTORY_SEPARATOR . 'Support');
        $this->createEmptyDirectory($supportDir . DIRECTORY_SEPARATOR . 'Data');
        $this->createDirectoryFor($supportDir . DIRECTORY_SEPARATOR . '_generated');
        $this->gitIgnore($outputDir);
        $this->gitIgnore($supportDir . DIRECTORY_SEPARATOR . '_generated');

        $configFile = (new Template($this->configTemplate))
            ->place('baseDir', $dir)
            ->place('tester', $haveTester ? $this->testerAndModules : '')
            ->produce();

        $namespace = rtrim($this->namespace, '\\');
        $configFile = "namespace: $namespace\nsupport_namespace: {$this->supportNamespace}\n" . $configFile;

        $this->createFile('codeception.yml', $configFile);

        if (!class_exists(Asserts::class)) {
            $this->addModulesToComposer(['Asserts']);
        }

        if ($haveTester) {
            $settings = Yaml::parse($configFile)['suites']['unit'];
            $settings['support_namespace'] = $this->supportNamespace;
            $this->createActor('UnitTester', $supportDir, $settings);
        }

        $this->gitIgnore($outputDir);
        $this->sayInfo("Created test directory inside at {$dir}");

        $this->say();
        $this->saySuccess("INSTALLATION COMPLETE");
        $this->say();
        $this->say('Unit tests will be executed in random order');
        $this->say('Use @depends annotation to change the order of tests');

        if ($haveTester) {
            $this->say('To access DI, ORM, Database enable corresponding modules in codeception.yml');
            $this->say('Use <bold>$this->tester</bold> object inside Codeception\Test\Unit to call their methods');
            $this->say("For example: \$this->tester->seeInDatabase('users', ['name' => 'davert'])");
        }

        $this->say();
        $this->say("<bold>Next steps:</bold>");
        $this->say("Create the first test using <comment>codecept g:test unit MyTest</comment>");
        $this->say("Run tests with <comment>codecept run</comment>");
        $this->say("<bold>Happy testing!</bold>");
    }
}
