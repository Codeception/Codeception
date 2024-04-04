<?php

declare(strict_types=1);

use Codeception\Attribute\Group;
use Codeception\Module\UniversalFramework as UniversalFrameworkModule;
use Codeception\PHPUnit\TestCase;

class ConfigurationTest extends TestCase
{
    public array $config = [];

    public function _setUp()
    {
        $this->config = \Codeception\Configuration::config();
    }

    protected function _tearDown()
    {
        UniversalFrameworkModule::$includeInheritedActions = true;
        UniversalFrameworkModule::$onlyActions = [];
        UniversalFrameworkModule::$excludeActions = [];
    }

    #[Group('core')]
    public function testSuites()
    {
        $suites = \Codeception\Configuration::suites();
        $this->assertContains('unit', $suites);
        $this->assertContains('cli', $suites);
    }

    #[Group('core')]
    public function testFunctionForStrippingClassNames()
    {
        $matches = [];
        $this->assertSame(1, preg_match('#\\\?(\w*?Helper)$#', '\\Codeception\\Module\\UserHelper', $matches));
        $this->assertSame('UserHelper', $matches[1]);
        $this->assertSame(1, preg_match('#\\\?(\w*?Helper)$#', 'UserHelper', $matches));
        $this->assertSame('UserHelper', $matches[1]);
    }

    #[Group('core')]
    public function testModules()
    {
        $settings = ['modules' => ['enabled' => ['EmulateModuleHelper']]];
        $modules = \Codeception\Configuration::modules($settings);
        $this->assertContains('EmulateModuleHelper', $modules);
        $settings = ['modules' => [
            'enabled' => ['EmulateModuleHelper'],
            'disabled' => ['EmulateModuleHelper'],
        ]];
        $modules = \Codeception\Configuration::modules($settings);
        $this->assertNotContains('EmulateModuleHelper', $modules);
    }

    #[Group('core')]
    public function testDefaultCustomCommandConfig()
    {
        $defaultConfig = \Codeception\Configuration::$defaultConfig;

        $this->assertArrayHasKey('extensions', $defaultConfig);

        $commandsConfig = $defaultConfig['extensions'];
        $this->assertArrayHasKey('commands', $commandsConfig);

        $this->assertArrayHasKey('extends', $defaultConfig);
        $this->assertNull($defaultConfig['extends']);
    }
}
