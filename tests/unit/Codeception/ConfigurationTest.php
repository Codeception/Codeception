<?php

declare(strict_types=1);

class ConfigurationTest extends \Codeception\PHPUnit\TestCase
{
    /**
     * @var array
     */
    public $config = [];

    public function _setUp()
    {
        $this->config = \Codeception\Configuration::config();
    }

    protected function _tearDown()
    {
        \Codeception\Module\UniversalFramework::$includeInheritedActions = true;
        \Codeception\Module\UniversalFramework::$onlyActions = [];
        \Codeception\Module\UniversalFramework::$excludeActions = [];
    }

    /**
     * @group core
     */
    public function testSuites()
    {
        $suites = \Codeception\Configuration::suites();
        $this->assertContains('unit', $suites);
        $this->assertContains('cli', $suites);
    }

    /**
     * @group core
     */
    public function testFunctionForStrippingClassNames()
    {
        $matches = [];
        $this->assertSame(1, preg_match('~\\\\?(\\w*?Helper)$~', '\\Codeception\\Module\\UserHelper', $matches));
        $this->assertSame('UserHelper', $matches[1]);
        $this->assertSame(1, preg_match('~\\\\?(\\w*?Helper)$~', 'UserHelper', $matches));
        $this->assertSame('UserHelper', $matches[1]);
    }

    /**
     * @group core
     */
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

    /**
     * @group core
     */
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
