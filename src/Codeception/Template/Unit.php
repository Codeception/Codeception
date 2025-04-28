<?php

declare(strict_types=1);

namespace Codeception\Template;

use Codeception\InitTemplate;
use Codeception\Template\Shared\TemplateHelpersTrait;
use Codeception\Util\Template;
use Symfony\Component\Yaml\Yaml;

class Unit extends InitTemplate
{
    use TemplateHelpersTrait;

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
        $dir = $this->ask('Where tests will be stored?', 'tests');

        if ($this->namespace === '') {
            $this->namespace = $this->ask('Enter a default namespace for tests (or skip this step)');
        }

        $this->say();
        $this->say('Codeception provides additional features for integration tests');
        $this->say('Like accessing frameworks, ORM, Database.');
        $haveTester = $this->ask('Do you wish to enable them?', false);
        $this->createSuiteDirs($dir);
        $this->sayInfo("Created test directory at {$dir}");

        $config = (new Template($this->configTemplate))
            ->place('baseDir', $dir)
            ->place('tester', $haveTester ? $this->testerAndModules : '')
            ->produce();

        $namespace     = rtrim($this->namespace, '\\');
        $config = "namespace: {$namespace}\nsupport_namespace: {$this->supportNamespace}\n" . $config;
        $this->createFile('codeception.yml', $config);
        $this->ensureModules(['Asserts']);
        if ($haveTester) {
            $settings = Yaml::parse($config)['suites']['unit'];
            $settings['support_namespace'] = $this->supportNamespace;
            $this->createActor('UnitTester', $dir . DIRECTORY_SEPARATOR . 'Support', $settings);
        }

        $this->saySuccess('INSTALLATION COMPLETE');
        $this->say();
        $this->say('Unit tests run in random order; use @depends to control.');

        if ($haveTester) {
            $this->say('To access DI, ORM, Database enable corresponding modules in codeception.yml');
            $this->say('Use <bold>$this->tester</bold> object inside Codeception\Test\Unit to call their methods');
            $this->say("For example: \$this->tester->seeInDatabase('users', ['name' => 'davert'])");
        }

        $this->say();
        $this->say('<bold>Next steps:</bold>');
        $this->say('1. Generate a test: <comment>codecept g:test unit MyTest</comment>');
        $this->say('2. Run tests: <comment>codecept run</comment>');
        $this->say('<bold>Happy testing!</bold>');
    }
}
