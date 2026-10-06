<?php

declare(strict_types=1);

use Codeception\Config\ConfigInterface;
use Codeception\Config\GlobalConfig;
use Codeception\Config\SuiteConfig;
use Codeception\Lib\Generator\PhpConfigFile;
use Codeception\PHPUnit\TestCase;

final class PhpConfigFileTest extends TestCase
{
    public function testGlobalRoundTripProducesEqualArray(): void
    {
        $config = [
            'namespace'    => 'App\\Tests',
            'paths'        => ['tests' => 'tests', 'output' => 'tests/_output'],
            'actor_suffix' => 'Tester',
            'settings'     => ['shuffle' => true, 'colors' => false],
            'modules'      => ['enabled' => ['Asserts', ['Db' => ['dsn' => 'sqlite::memory:']]], 'config' => ['Db' => ['populate' => true]]],
            'extensions'   => ['enabled' => ['Codeception\\Extension\\RunFailed'], 'commands' => ['My\\Command']],
            'suites'       => ['Unit' => ['actor' => 'UnitTester', 'modules' => ['enabled' => ['Asserts']]]],
            'groups'       => ['slow' => ['tests/slow']],
        ];

        $loaded = $this->load((new PhpConfigFile())->renderGlobal($config));

        $this->assertInstanceOf(GlobalConfig::class, $loaded);
        $this->assertEquals($config, $loaded->toArray());
    }

    public function testSuiteRoundTripProducesEqualArray(): void
    {
        $config = [
            'actor'           => 'FunctionalTester',
            'modules'         => ['enabled' => [['Symfony' => ['app_path' => 'src']], ['Doctrine' => ['depends' => 'Symfony']]]],
            'step_decorators' => null,
            'error_level'     => 'E_ALL & ~E_DEPRECATED',
        ];

        $loaded = $this->load((new PhpConfigFile())->renderSuite($config));

        $this->assertInstanceOf(SuiteConfig::class, $loaded);
        $this->assertEquals($config, $loaded->toArray());
    }

    public function testUnknownKeysRouteThroughMerge(): void
    {
        $source = (new PhpConfigFile())->renderGlobal(['reporters' => ['report' => 'Custom']]);

        $this->assertStringContainsString('->merge(', $source);
        $this->assertEquals(['reporters' => ['report' => 'Custom']], $this->load($source)->toArray());
    }

    public function testWholeValueParamBecomesGetenvInGlobal(): void
    {
        $source = (new PhpConfigFile())->renderGlobal(['modules' => ['enabled' => [['Db' => ['dsn' => '%DB_DSN%']]]]]);

        $this->assertStringContainsString("getenv('DB_DSN')", $source);
    }

    public function testWholeValueParamBecomesParamsGetInSuite(): void
    {
        $renderer = new PhpConfigFile();
        $source = $renderer->renderSuite(['modules' => ['enabled' => [['Db' => ['dsn' => '%DB_DSN%']]]]]);

        $this->assertStringContainsString("\\Codeception\\Config\\Params::get('DB_DSN')", $source);
        $this->assertSame([], $renderer->warnings());
    }

    public function testEmbeddedParamIsReported(): void
    {
        $renderer = new PhpConfigFile();
        $renderer->renderSuite(['modules' => ['enabled' => [['Db' => ['dsn' => 'mysql://%HOST%/db']]]]]);

        $this->assertNotEmpty($renderer->warnings());
    }

    public function testDottedParamNameIsReportedNotSilentlyDropped(): void
    {
        $renderer = new PhpConfigFile();
        $source = $renderer->renderSuite(['modules' => ['enabled' => [['Db' => ['dsn' => '%db.host%']]]]]);

        $this->assertStringContainsString('%db.host%', $source, 'kept verbatim');
        $this->assertNotEmpty($renderer->warnings(), 'and the user is warned');
    }

    public function testSuiteOnlyKeysInGlobalRouteThroughMerge(): void
    {
        $config = [
            'env'     => ['staging' => ['modules' => ['config' => ['Db' => ['dsn' => 'x']]]]],
            'formats' => ['Custom'],
        ];

        $loaded = $this->load((new PhpConfigFile())->renderGlobal($config));

        $this->assertInstanceOf(GlobalConfig::class, $loaded);
        $this->assertEquals($config, $loaded->toArray());
    }

    public function testGlobalOnlyKeysInSuiteRouteThroughMerge(): void
    {
        $config = [
            'gherkin' => ['contexts' => ['default' => ['App\\Context']]],
            'params'  => ['.env'],
        ];

        $loaded = $this->load((new PhpConfigFile())->renderSuite($config));

        $this->assertInstanceOf(SuiteConfig::class, $loaded);
        $this->assertEquals($config, $loaded->toArray());
    }

    public function testModulesDisabledIsPreserved(): void
    {
        $config = ['modules' => ['enabled' => ['X'], 'disabled' => ['Y']]];

        $loaded = $this->load((new PhpConfigFile())->renderGlobal($config));

        $this->assertSame(['X'], $loaded->toArray()['modules']['enabled']);
        $this->assertSame(['Y'], $loaded->toArray()['modules']['disabled']);
    }

    public function testNullModuleBodyRendersBareModule(): void
    {
        $loaded = $this->load((new PhpConfigFile())->renderSuite(['modules' => ['enabled' => [['X' => null]]]]));

        $this->assertSame(['X'], $loaded->toArray()['modules']['enabled']);
    }

    public function testMultiModuleListEntryKeepsEveryModule(): void
    {
        $config = ['modules' => ['enabled' => [['Db' => ['a' => 1], 'Redis' => ['b' => 2]]]]];

        $loaded = $this->load((new PhpConfigFile())->renderSuite($config));

        $this->assertSame(
            [['Db' => ['a' => 1]], ['Redis' => ['b' => 2]]],
            $loaded->toArray()['modules']['enabled']
        );
    }

    private function load(string $source): ConfigInterface
    {
        $file = codecept_output_dir() . 'render_' . uniqid() . '.php';
        file_put_contents($file, $source);
        try {
            $result = (static fn () => require $file)();
        } finally {
            @unlink($file);
        }
        $this->assertInstanceOf(ConfigInterface::class, $result);
        return $result;
    }
}
