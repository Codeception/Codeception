<?php

declare(strict_types=1);

use Codeception\Config\AbstractConfigBuilder;
use Codeception\Config\GlobalConfig;
use Codeception\Config\SuiteConfig;
use Codeception\Configuration;
use Codeception\Lib\Generator\PhpConfigFile;
use Codeception\PHPUnit\TestCase;

/**
 * Guards the builder API against silently lagging the core config keys.
 * If someone adds a key to Configuration::$defaultConfig / $defaultSuiteSettings without a
 * matching builder method, one of these tests fails — keeping the PHP format at full parity.
 */
final class ConfigDriftTest extends TestCase
{
    private const GLOBAL_KEY_METHOD = [
        'actor_suffix'      => 'actorSuffix',
        'support_namespace' => 'supportNamespace',
        'namespace'         => 'namespace',
        'include'           => 'include',
        'paths'             => 'paths',
        'extends'           => 'extends',
        'suites'            => 'suite',
        'modules'           => 'module',
        'extensions'        => 'extension',
        'groups'            => 'groups',
        'bootstrap'         => 'bootstrap',
        'settings'          => 'settings',
        'coverage'          => 'coverage',
        'params'            => 'params',
        'gherkin'           => 'gherkin',
    ];

    private const SUITE_KEY_METHOD = [
        'actor'                              => 'actor',
        'modules'                            => 'module',
        'step_decorators'                    => 'stepDecorators',
        'path'                               => 'path',
        'extends'                            => 'extends',
        'namespace'                          => 'namespace',
        'groups'                             => 'groups',
        'formats'                            => 'formats',
        'shuffle'                            => 'shuffle',
        'extensions'                         => 'extension',
        'error_level'                        => 'errorLevel',
        'convert_deprecations_to_exceptions' => 'convertDeprecationsToExceptions',
    ];

    public function testEveryGlobalKeyHasABuilderMethod(): void
    {
        foreach (array_keys(Configuration::$defaultConfig) as $key) {
            $this->assertArrayHasKey($key, self::GLOBAL_KEY_METHOD, "GlobalConfig has no method mapped for '{$key}'");
            $this->assertTrue($this->hasMethod(GlobalConfig::class, self::GLOBAL_KEY_METHOD[$key]));
        }
    }

    public function testEverySuiteKeyHasABuilderMethod(): void
    {
        foreach (array_keys(Configuration::$defaultSuiteSettings) as $key) {
            $this->assertArrayHasKey($key, self::SUITE_KEY_METHOD, "SuiteConfig has no method mapped for '{$key}'");
            $this->assertTrue($this->hasMethod(SuiteConfig::class, self::SUITE_KEY_METHOD[$key]));
        }
    }

    public function testSettingsMethodCoversEverySettingKey(): void
    {
        $covered = [];
        foreach ((new ReflectionMethod(GlobalConfig::class, 'settings'))->getParameters() as $parameter) {
            $covered[] = strtolower((string) preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $parameter->getName()));
        }

        foreach (array_keys(Configuration::$defaultConfig['settings']) as $key) {
            $this->assertContains($key, $covered, "settings() has no named parameter for '{$key}'");
        }
    }

    public function testPhpConfigFileSettingsMapCoversEverySettingKey(): void
    {
        $map = (new ReflectionClassConstant(PhpConfigFile::class, 'SETTINGS_MAP'))->getValue();

        foreach (array_keys(Configuration::$defaultConfig['settings']) as $key) {
            $this->assertArrayHasKey($key, $map, "PhpConfigFile::SETTINGS_MAP has no entry for setting '{$key}'");
        }

        $params = [];
        foreach ((new ReflectionMethod(GlobalConfig::class, 'settings'))->getParameters() as $parameter) {
            $params[$parameter->getName()] = true;
        }
        foreach ($map as $snake => $camel) {
            $this->assertArrayHasKey($camel, $params, "SETTINGS_MAP maps '{$snake}' to unknown settings() parameter '{$camel}'");
        }
    }

    private function hasMethod(string $class, string $method): bool
    {
        return method_exists($class, $method) || method_exists(AbstractConfigBuilder::class, $method);
    }
}
