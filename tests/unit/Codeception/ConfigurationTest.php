<?php

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    public function setUp() {
        $this->config = \Codeception\Configuration::config();
    }

    protected function tearDown()
    {
        \Codeception\Module\PhpSiteHelper::$includeInheritedActions = true;
        \Codeception\Module\PhpSiteHelper::$onlyActions = array();
        \Codeception\Module\PhpSiteHelper::$excludeActions = array();
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
    public function testActionsInExtendedButNotInheritedModule()
    {
        \Codeception\Module\PhpSiteHelper::$includeInheritedActions = false;
        $modules = array('PhpSiteHelper' => new \Codeception\Module\PhpSiteHelper());
        $actions = \Codeception\Configuration::actions($modules);
        $this->assertArrayNotHasKey('amOnPage', $actions);
        $this->assertArrayNotHasKey('see', $actions);
        $this->assertArrayNotHasKey('click', $actions);
    }

    /**
     * @group core
     */
    public function testExplicitlySetActionsOnNotInherited()
    {
        \Codeception\Module\PhpSiteHelper::$includeInheritedActions = false;
        \Codeception\Module\PhpSiteHelper::$onlyActions = array('see');
        $modules = array('PhpSiteHelper' => new \Codeception\Module\PhpSiteHelper());
        $actions = \Codeception\Configuration::actions($modules);
        $this->assertArrayNotHasKey('amOnPage', $actions);
        $this->assertArrayHasKey('see', $actions);
        $this->assertArrayNotHasKey('click', $actions);
    }

    /**
     * @group core
     */
    public function testActionsExplicitlySetForNotInheritedModule()
    {
        \Codeception\Module\PhpSiteHelper::$onlyActions = array('see');
        $modules = array('PhpSiteHelper' => new \Codeception\Module\PhpSiteHelper());
        $actions = \Codeception\Configuration::actions($modules);
        $this->assertArrayNotHasKey('amOnPage', $actions);
        $this->assertArrayHasKey('see', $actions);
    }

    /**
     * @group core
     */
    public function testCreateModuleWithoutRequiredFields()
    {
        $this->setExpectedException('\Codeception\Exception\ModuleConfig');

        $class = 'StubModule';
        new $class(array('secondField' => 'none'));
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

        $module = new $class($config);

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

        $class = 'StubModule';
        $module = new $class($config);
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