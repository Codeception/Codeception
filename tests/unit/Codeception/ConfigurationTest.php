<?php


class ConfigurationTest extends \PHPUnit\Framework\TestCase
{

    public function setUp()
    {
        $this->config = \Codeception\Configuration::config();
    }

    protected function tearDown()
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
        $matches = array();
        $this->assertEquals(1, preg_match('~\\\\?(\\w*?Helper)$~', '\\Codeception\\Module\\UserHelper', $matches));
        $this->assertEquals('UserHelper', $matches[1]);
        $this->assertEquals(1, preg_match('~\\\\?(\\w*?Helper)$~', 'UserHelper', $matches));
        $this->assertEquals('UserHelper', $matches[1]);
    }

    /**
     * @group core
     */
    public function testModules()
    {
        $settings = array('modules' => array('enabled' => array('EmulateModuleHelper')));
        $modules = \Codeception\Configuration::modules($settings);
        $this->assertContains('EmulateModuleHelper', $modules);
        $settings = array('modules' => array(
            'enabled' => array('EmulateModuleHelper'),
            'disabled' => array('EmulateModuleHelper'),
        ));
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
    }
}
