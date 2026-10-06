<?php

declare(strict_types=1);

use Codeception\Config\ConfigInterface;
use Codeception\Config\GlobalConfig;
use Codeception\Config\SuiteConfig;
use Codeception\Extension\RunFailed;
use Codeception\PHPUnit\TestCase;

final class GlobalConfigTest extends TestCase
{
    public function testImplementsContract(): void
    {
        $this->assertInstanceOf(ConfigInterface::class, GlobalConfig::create());
    }

    public function testBuildsExpectedArray(): void
    {
        $config = GlobalConfig::create()
            ->namespace('App\\Tests')
            ->supportNamespace('Support')
            ->paths(tests: 'tests', output: 'tests/_output')
            ->actorSuffix('Tester')
            ->extension(RunFailed::class)
            ->module('Db', ['dsn' => 'sqlite::memory:'])
            ->module('Asserts')
            ->settings(shuffle: true, colors: false, reportUselessTests: true)
            ->params('.env', '.env.test')
            ->toArray();

        $this->assertSame('App\\Tests', $config['namespace']);
        $this->assertSame('Support', $config['support_namespace']);
        $this->assertSame(['tests' => 'tests', 'output' => 'tests/_output'], $config['paths']);
        $this->assertSame('Tester', $config['actor_suffix']);
        $this->assertSame([RunFailed::class], $config['extensions']['enabled']);
        $this->assertSame([['Db' => ['dsn' => 'sqlite::memory:']], 'Asserts'], $config['modules']['enabled']);
        $this->assertSame(['shuffle' => true, 'colors' => false, 'report_useless_tests' => true], $config['settings']);
        $this->assertSame(['.env', '.env.test'], $config['params']);
    }

    public function testSettingsCamelCaseMapsToSnakeCase(): void
    {
        $config = GlobalConfig::create()
            ->settings(memoryLimit: '1G', backupGlobals: false, beStrictAboutChangesToGlobalState: true)
            ->toArray();

        $this->assertSame([
            'memory_limit'                            => '1G',
            'backup_globals'                          => false,
            'be_strict_about_changes_to_global_state' => true,
        ], $config['settings']);
    }

    public function testSettingsOmitsNullArguments(): void
    {
        $config = GlobalConfig::create()->settings(shuffle: true)->toArray();

        $this->assertSame(['shuffle' => true], $config['settings']);
    }

    public function testModuleConfigAndCommands(): void
    {
        $config = GlobalConfig::create()
            ->moduleConfig('Db', ['populate' => true])
            ->commands('My\\Command', 'My\\Other')
            ->toArray();

        $this->assertSame(['Db' => ['populate' => true]], $config['modules']['config']);
        $this->assertSame(['My\\Command', 'My\\Other'], $config['extensions']['commands']);
    }

    public function testInlineSuiteIsNormalizedToArray(): void
    {
        $config = GlobalConfig::create()
            ->suite('Unit', SuiteConfig::create()->actor('UnitTester')->module('Asserts'))
            ->toArray();

        $this->assertSame(
            ['actor' => 'UnitTester', 'modules' => ['enabled' => ['Asserts']]],
            $config['suites']['Unit']
        );
    }

    public function testInlineParamMapIsAllowed(): void
    {
        $config = GlobalConfig::create()->params(['token' => 'abc'], '.env')->toArray();

        $this->assertSame([['token' => 'abc'], '.env'], $config['params']);
    }

    public function testOnlySetKeysAreEmitted(): void
    {
        $this->assertSame(['namespace' => 'X'], GlobalConfig::create()->namespace('X')->toArray());
    }

    public function testMergeOverridesWinLast(): void
    {
        $config = GlobalConfig::create()
            ->namespace('A')
            ->merge(['namespace' => 'B', 'custom' => 123])
            ->toArray();

        $this->assertSame('B', $config['namespace']);
        $this->assertSame(123, $config['custom']);
    }

    public function testExtensionWithConfig(): void
    {
        $config = GlobalConfig::create()->extension('My\\Ext', ['opt' => 1])->toArray();

        $this->assertSame(['My\\Ext'], $config['extensions']['enabled']);
        $this->assertSame(['My\\Ext' => ['opt' => 1]], $config['extensions']['config']);
    }

    public function testEmptySuiteNameIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        GlobalConfig::create()->suite('', []);
    }
}
