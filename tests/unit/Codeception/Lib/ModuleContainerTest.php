<?php
namespace Codeception\Lib;

use Codeception\Util\Stub;

class ModuleContainerTest extends \PHPUnit_Framework_TestCase
{
    use \Codeception\Specify;
    /**
     * @var ModuleContainer
     */
    protected $moduleContainer;

    protected function setUp()
    {
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), []);
    }

    /**
     * @group core
     * @throws \Codeception\Exception\Configuration
     */
    public function testCreateModule()
    {
        $module = $this->moduleContainer->create('EmulateModuleHelper');
        $this->assertInstanceOf('Codeception\Module\EmulateModuleHelper', $module);

        $module = $this->moduleContainer->create('Codeception\Module\EmulateModuleHelper');
        $this->assertInstanceOf('Codeception\Module\EmulateModuleHelper', $module);

        $this->assertTrue($this->moduleContainer->hasModule('EmulateModuleHelper'));
        $this->assertInstanceOf('Codeception\Module\EmulateModuleHelper', $this->moduleContainer->getModule('EmulateModuleHelper'));

    }

    /**
     * @group core
     */
    public function testActions()
    {
        $this->moduleContainer->create('EmulateModuleHelper');
        $actions = $this->moduleContainer->getActions();
        $this->assertArrayHasKey('seeEquals', $actions);
        $this->assertEquals('EmulateModuleHelper', $actions['seeEquals']);
    }

    /**
     * @group core
     */
    public function testActionsInExtendedModule()
    {
        $this->moduleContainer->create('\Codeception\Module\PhpSiteHelper');
        $actions = $this->moduleContainer->getActions();
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
        $this->moduleContainer->create('\Codeception\Module\PhpSiteHelper');
        $actions = $this->moduleContainer->getActions();
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
        $this->moduleContainer->create('\Codeception\Module\PhpSiteHelper');
        $actions = $this->moduleContainer->getActions();
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
        $this->moduleContainer->create('\Codeception\Module\PhpSiteHelper');
        $actions = $this->moduleContainer->getActions();
        $this->assertArrayNotHasKey('amOnPage', $actions);
        $this->assertArrayHasKey('see', $actions);
    }

    /**
     * @group core
     */
    public function testCreateModuleWithoutRequiredFields()
    {
        $this->setExpectedException('\Codeception\Exception\ModuleConfig');
        $this->moduleContainer->create('Codeception\Lib\StubModule', array('secondField' => 'none'));
    }

    /**
     * @group core
     */
    public function testCreateModuleWithCorrectConfig()
    {
        $config = ['modules' =>
            ['config' => [
                'Codeception\Lib\StubModule' => [
                    'firstField' => 'firstValue',
                    'secondField' => 'secondValue',
                ]
            ]
        ]];

        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), $config);
        $module = $this->moduleContainer->create('Codeception\Lib\StubModule');

        $this->assertEquals('firstValue', $module->_getFirstField());
        $this->assertEquals('secondValue', $module->_getSecondField());
    }

    /**
     * @group core
     */
    public function testReconfigureModule()
    {
        $config = ['modules' =>
            ['config' => [
                'Codeception\Lib\StubModule' => [
                    'firstField' => 'firstValue',
                    'secondField' => 'secondValue',
                ]
            ]
        ]];
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), $config);
        $module = $this->moduleContainer->create('Codeception\Lib\StubModule');
        $module->_reconfigure(array('firstField' => '1st', 'secondField' => '2nd'));
        $this->assertEquals('1st', $module->_getFirstField());
        $this->assertEquals('2nd', $module->_getSecondField());
        $module->_resetConfig();
        $this->assertEquals('firstValue', $module->_getFirstField());
        $this->assertEquals('secondValue', $module->_getSecondField());
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

