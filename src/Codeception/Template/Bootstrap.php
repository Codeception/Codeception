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
    protected string $supportDir      = 'tests/Support';
    protected string $dataDir         = 'tests/Support/Data';
    protected string $envsDir         = 'tests/_envs';
    protected string $outputDir       = 'tests/_output';
    protected string $namespace       = 'Tests';
    protected string $supportNamespace = 'Support';
    protected bool $php               = false;

    private function ext(): string
    {
        return $this->php ? 'php' : 'yml';
    }

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

        $this->php = $input->hasOption('php') && (bool) $input->getOption('php');

        $this->say("<fg=white;bg=magenta> Bootstrapping Codeception </fg=white;bg=magenta>\n");
        $this->createGlobalConfig();
        $this->say('File codeception.' . $this->ext() . ' created       <- global configuration');

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
        $ext = $this->ext();
        $this->say('<bold>Next steps:</bold>');
        $this->say("1. Edit <bold>tests/Acceptance.suite.{$ext}</bold> to set url of your application. Change PhpBrowser to WebDriver to enable browser testing");
        $this->say("2. Edit <bold>tests/Functional.suite.{$ext}</bold> to enable a framework module. Remove this file if you don't use a framework");
        $this->say('3. Create your first acceptance tests using <comment>codecept g:cest Acceptance First</comment>');
        $this->say('4. Write first test in <bold>tests/Acceptance/FirstCest.php</bold>');
        $this->say('5. Run tests using: <comment>codecept run</comment>');
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
        $config = <<<EOF
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
        $phpConfig = <<<EOF
<?php

declare(strict_types=1);

use Codeception\\Config\\SuiteConfig;

return SuiteConfig::create()
    ->actor('{$this->phpLiteral($actor . $this->actorSuffix)}')
    // ->module('Symfony')  <- add a framework module here
    ->stepDecorators(null);

EOF;
        $this->createSuite('Functional', $actor, $config, $phpConfig);
        $this->say("tests/Functional/ created          <- functional tests");
        $this->say("tests/Functional.suite.{$this->ext()} written <- functional test suite configuration");
    }

    protected function createAcceptanceSuite(string $actor = 'Acceptance'): void
    {
        $config = <<<EOF
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
        $phpConfig = <<<EOF
<?php

declare(strict_types=1);

use Codeception\\Config\\SuiteConfig;
use Codeception\\Step\\ConditionalAssertion;
use Codeception\\Step\\Retry;
use Codeception\\Step\\TryTo;

return SuiteConfig::create()
    ->actor('{$this->phpLiteral($actor . $this->actorSuffix)}')
    ->module('PhpBrowser', ['url' => 'http://localhost/myapp'])
    ->stepDecorators([
        ConditionalAssertion::class,
        TryTo::class,
        Retry::class,
    ]);

EOF;
        $this->createSuite('Acceptance', $actor, $config, $phpConfig);
        $this->say("tests/Acceptance/ created          <- acceptance tests");
        $this->say("tests/Acceptance.suite.{$this->ext()} written <- acceptance test suite configuration");
    }

    protected function createUnitSuite(string $actor = 'Unit'): void
    {
        $config = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for unit or integration tests.

actor: {$actor}{$this->actorSuffix}
modules:
    enabled:
        - Asserts
step_decorators: ~

EOF;
        $phpConfig = <<<EOF
<?php

declare(strict_types=1);

use Codeception\\Config\\SuiteConfig;

return SuiteConfig::create()
    ->actor('{$this->phpLiteral($actor . $this->actorSuffix)}')
    ->module('Asserts')
    ->stepDecorators(null);

EOF;
        $this->createSuite('Unit', $actor, $config, $phpConfig);
        $this->say("tests/Unit/ created                <- unit tests");
        $this->say("tests/Unit.suite.{$this->ext()} written       <- unit test suite configuration");
    }

    public function createGlobalConfig(): void
    {
        if ($this->php) {
            $this->createFile('codeception.php', $this->globalPhpConfig());
            return;
        }

        $config = [
            'support_namespace' => $this->supportNamespace,
            'paths' => [
                'tests'   => 'tests',
                'output'  => $this->outputDir,
                'data'    => $this->dataDir,
                'support' => $this->supportDir,
                'envs'    => $this->envsDir,
            ],
            'actor_suffix' => 'Tester',
            'extensions'   => ['enabled' => [RunFailed::class]],
        ];

        $yaml = Yaml::dump($config, 4);
        if ($this->namespace) {
            $yaml = "namespace: {$this->namespace}\n" . $yaml;
        }
        $this->createFile('codeception.yml', $yaml);
    }

    protected function globalPhpConfig(): string
    {
        $namespaceLine = $this->namespace ? "\n    ->namespace('{$this->phpLiteral($this->namespace)}')" : '';
        $support = $this->phpLiteral($this->supportNamespace);
        $output  = $this->phpLiteral($this->outputDir);
        $data    = $this->phpLiteral($this->dataDir);
        $supportDir = $this->phpLiteral($this->supportDir);
        $envs    = $this->phpLiteral($this->envsDir);

        return <<<EOF
<?php

declare(strict_types=1);

use Codeception\\Config\\GlobalConfig;
use Codeception\\Extension\\RunFailed;

return GlobalConfig::create(){$namespaceLine}
    ->supportNamespace('{$support}')
    ->paths(
        tests: 'tests',
        output: '{$output}',
        data: '{$data}',
        support: '{$supportDir}',
        envs: '{$envs}',
    )
    ->actorSuffix('Tester')
    ->extension(RunFailed::class);

EOF;
    }

    protected function createSuite(string $name, string $actor, string $config, ?string $phpConfig = null): void
    {
        $dir      = 'tests' . DIRECTORY_SEPARATOR . $name;
        $filename = "{$name}.suite." . $this->ext();
        $this->createDirectoryFor($dir, $filename);
        $file = 'tests' . DIRECTORY_SEPARATOR . $filename;
        $this->createFile($file, $this->php ? (string) $phpConfig : $config);

        if ($this->php) {
            /** @var \Codeception\Config\SuiteConfig $suiteConfig */
            $suiteConfig = require getcwd() . DIRECTORY_SEPARATOR . $file;
            $settings = $suiteConfig->toArray();
        } else {
            $settings = Yaml::parse($config);
        }
        $settings['support_namespace'] = $this->supportNamespace;
        $this->createActor($actor . $this->actorSuffix, $this->supportDir, $settings);
    }
}
