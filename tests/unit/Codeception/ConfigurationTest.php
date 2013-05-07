<?php

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    public function setUp() {
        $this->config = \Codeception\Configuration::config();
    }

    /**
     * @group core
     */
    public function testModules() {
        $settings = array('modules' => array('enabled' => array('EmulateModuleHelper')), 'class_name' => 'CodeGuy','path' => $this->config['paths']['tests'].'/unit');
        $modules = \Codeception\Configuration::modules($settings);
        $this->assertArrayHasKey('EmulateModuleHelper', $modules);
        $this->assertTrue(method_exists($modules['EmulateModuleHelper'], 'seeEquals'));
    }

    /**
     * @group core
     */
    public function testActions()
    {
        $modules = array('EmulateModuleHelper' => new \Codeception\Module\EmulateModuleHelper);
        $actions = \Codeception\Configuration::actions($modules);
        $this->assertArrayHasKey('seeEquals', $actions);
        $this->assertEquals('EmulateModuleHelper',$actions['seeEquals']);
    }

    /**
     * @group core
     */
    public function testActionsInExtendedModule()
    {
        $modules = array('PhpSiteHelper' => new \Codeception\Module\PhpSiteHelper());
        $actions = \Codeception\Configuration::actions($modules);
        $this->assertArrayHasKey('amOnPage', $actions);
        $this->assertArrayHasKey('see', $actions);
        $this->assertArrayHasKey('click', $actions);
    }

}
