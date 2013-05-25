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

    /**
     * @group core
     */
    public function testCreateModuleWithoutRequiredFields()
    {
        $this->setExpectedException('\Codeception\Exception\ModuleConfig');

        $class = 'StubModule';
        $module = \Codeception\Configuration::createModule($class);
    }

    /**
     * @group core
     */
    public function testCreateModuleWithCorrectConfig()
    {
        $class = 'StubModule';
        $config = array(
            'firstField'     => 'firstValue',
            'secondField' => 'secondValue',
        );

        $module = \Codeception\Configuration::createModule($class,$config);

        $this->assertEquals($config['firstField'],$module->_getFirstField());
        $this->assertEquals($config['secondField'],$module->_getSecondField());
    }

    /**
     * @group core
     */
    public function testReconfigureModule()
    {
        $config = array(
            'firstField'     => 'firstValue',
            'secondField' => 'secondValue',
        );

        $module = \Codeception\Configuration::createModule('StubModule', $config);
        $module->_reconfigure(array('firstField' => '1st', 'secondField' => '2nd'));
        $this->assertEquals('1st',$module->_getFirstField());
        $this->assertEquals('2nd',$module->_getSecondField());
        $module->_resetConfig();
        $this->assertEquals($config['firstField'],$module->_getFirstField());
        $this->assertEquals($config['secondField'],$module->_getSecondField());
    }

}

class StubModule extends \Codeception\Module
{

    protected $requiredFields = array(
        'firstField',
        'secondField',
    );

    public function _getFirstField()
    {
        return $this->config['firstField'];
    }

    public function _getSecondField()
    {
        return $this->config['secondField'];
    }

}