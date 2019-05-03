<?php
namespace Codeception\Lib;

use Codeception\Lib\Interfaces\ConflictsWithModule;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Test\Unit;
use Codeception\Util\Stub;

// @codingStandardsIgnoreFile
class ModuleContainerTest extends Unit
{
    use \Codeception\Specify;
    /**
     * @var ModuleContainer
     */
    protected $moduleContainer;

    protected function _setUp()
    {
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), []);
    }

    protected function _tearDown()
    {
        \Codeception\Module\UniversalFramework::$includeInheritedActions = true;
        \Codeception\Module\UniversalFramework::$onlyActions = [];
        \Codeception\Module\UniversalFramework::$excludeActions = [];
        \Codeception\Module\UniversalFramework::$aliases = [];
    }

    /**
     * @group core
     * @throws \Codeception\Exception\ConfigurationException
     */
    public function testCreateModule()
    {
        $module = $this->moduleContainer->create('EmulateModuleHelper');
        $this->assertInstanceOf('Codeception\Module\EmulateModuleHelper', $module);

        $module = $this->moduleContainer->create('Codeception\Module\EmulateModuleHelper');
        $this->assertInstanceOf('Codeception\Module\EmulateModuleHelper', $module);

        $this->assertTrue($this->moduleContainer->hasModule('EmulateModuleHelper'));
        $this->assertInstanceOf(
            'Codeception\Module\EmulateModuleHelper',
            $this->moduleContainer->getModule('EmulateModuleHelper')
        );
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
        $this->moduleContainer->create('\Codeception\Module\UniversalFramework');
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
        \Codeception\Module\UniversalFramework::$includeInheritedActions = false;
        $this->moduleContainer->create('\Codeception\Module\UniversalFramework');
        $actions = $this->moduleContainer->getActions();
        $this->assertArrayNotHasKey('amOnPage', $actions);
        $this->assertArrayNotHasKey('see', $actions);
        $this->assertArrayNotHasKey('click', $actions);
        $this->assertArrayHasKey('useUniversalFramework', $actions);
    }

    /**
     * @group core
     */
    public function testExplicitlySetActionsOnNotInherited()
    {
        \Codeception\Module\UniversalFramework::$includeInheritedActions = false;
        \Codeception\Module\UniversalFramework::$onlyActions = ['see'];
        $this->moduleContainer->create('\Codeception\Module\UniversalFramework');
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
        \Codeception\Module\UniversalFramework::$onlyActions = ['see'];
        $this->moduleContainer->create('\Codeception\Module\UniversalFramework');
        $actions = $this->moduleContainer->getActions();
        $this->assertArrayNotHasKey('amOnPage', $actions);
        $this->assertArrayHasKey('see', $actions);
    }

    /**
     * @group core
     */
    public function testCreateModuleWithoutRequiredFields()
    {
        $this->expectException('\Codeception\Exception\ModuleConfigException');
        $this->moduleContainer->create('Codeception\Lib\StubModule');
    }

    /**
     * @group core
     */
    public function testCreateModuleWithCorrectConfig()
    {
        $config = [
            'modules' => [
                'config' => [
                    'Codeception\Lib\StubModule' => [
                        'firstField' => 'firstValue',
                        'secondField' => 'secondValue',
                    ]
                ]
            ]
        ];

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
        $config = [
            'modules' => [
                'config' => [
                    'Codeception\Lib\StubModule' => [
                        'firstField' => 'firstValue',
                        'secondField' => 'secondValue',
                    ]
                ]
            ]
        ];
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), $config);
        $module = $this->moduleContainer->create('Codeception\Lib\StubModule');
        $module->_reconfigure(['firstField' => '1st', 'secondField' => '2nd']);
        $this->assertEquals('1st', $module->_getFirstField());
        $this->assertEquals('2nd', $module->_getSecondField());
        $module->_resetConfig();
        $this->assertEquals('firstValue', $module->_getFirstField());
        $this->assertEquals('secondValue', $module->_getSecondField());
    }

    public function testConflictsByModuleName()
    {
        $this->expectException('Codeception\Exception\ModuleConflictException');
        $this->moduleContainer->create('Codeception\Lib\ConflictedModule');
        $this->moduleContainer->create('Cli');
        $this->moduleContainer->validateConflicts();
    }


    public function testConflictsByClass()
    {
        $this->expectException('Codeception\Exception\ModuleConflictException');
        $this->moduleContainer->create('Codeception\Lib\ConflictedModule2');
        $this->moduleContainer->create('Cli');
        $this->moduleContainer->validateConflicts();
    }

    public function testConflictsByInterface()
    {
        $this->expectException('Codeception\Exception\ModuleConflictException');
        $this->moduleContainer->create('Codeception\Lib\ConflictedModule3');
        $this->moduleContainer->create('Symfony2');
        $this->moduleContainer->validateConflicts();
    }

    public function testConflictsByWebInterface()
    {
        $this->expectException('Codeception\Exception\ModuleConflictException');
        $this->moduleContainer->create('Laravel5');
        $this->moduleContainer->create('Symfony2');
        $this->moduleContainer->validateConflicts();
    }

    public function testConflictsForREST()
    {
        $config = ['modules' =>
            ['config' => [
                'REST' => [
                    'depends' => 'ZF2',
                    ]
                ]
            ]
        ];
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), $config);
        $this->moduleContainer->create('ZF2');
        $this->moduleContainer->create('REST');
        $this->moduleContainer->validateConflicts();
    }

    public function testConflictsOnDependentModules()
    {
        $config = ['modules' =>
            ['config' => [
                'WebDriver' => ['url' => 'localhost', 'browser' => 'firefox'],
                'REST' => [
                    'depends' => 'PhpBrowser',
                    ]
                ]
            ]
        ];
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), $config);
        $this->moduleContainer->create('WebDriver');
        $this->moduleContainer->create('REST');
        $this->moduleContainer->validateConflicts();
    }


    public function testNoConflictsForPartedModules()
    {
        $config = ['modules' =>
            ['config' => [
                'Laravel5' => [
                    'part' => 'ORM',
                    ]
                ]
            ]
        ];
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), $config);
        $this->moduleContainer->create('Laravel5');
        $this->moduleContainer->create('Symfony2');
        $this->moduleContainer->validateConflicts();
    }

    public function testModuleDependenciesFail()
    {
        $this->expectException('Codeception\Exception\ModuleRequireException');
        $this->moduleContainer->create('Codeception\Lib\DependencyModule');
    }

    public function testModuleDependencies()
    {
        $config = ['modules' => [
            'enabled' => ['Codeception\Lib\DependencyModule'],
            'config' => [
                'Codeception\Lib\DependencyModule' => [
                    'depends' => 'Codeception\Lib\ConflictedModule'
                ]
            ]
        ]];
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), $config);
        $this->moduleContainer->create('Codeception\Lib\DependencyModule');
        $this->moduleContainer->hasModule('\Codeception\Lib\DependencyModule');
    }

    public function testModuleParts1()
    {
        $config = ['modules' => [
            'enabled' => ['\Codeception\Lib\PartedModule'],
            'config' => [
                '\Codeception\Lib\PartedModule' => [
                    'part' => 'one'
                    ]
                ]
            ]
        ];
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), $config);
        $this->moduleContainer->create('\Codeception\Lib\PartedModule');
        $actions = $this->moduleContainer->getActions();
        $this->assertArrayHasKey('partOne', $actions);
        $this->assertArrayNotHasKey('partTwo', $actions);
    }

    public function testModuleParts2()
    {
        $config = ['modules' => [
            'enabled' => ['\Codeception\Lib\PartedModule'],
            'config' => ['\Codeception\Lib\PartedModule' => [
                    'part' => ['Two']
                    ]
                ]
            ]
        ];
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), $config);
        $this->moduleContainer->create('\Codeception\Lib\PartedModule');
        $actions = $this->moduleContainer->getActions();
        $this->assertArrayHasKey('partTwo', $actions);
        $this->assertArrayNotHasKey('partOne', $actions);
    }

    public function testShortConfigParts()
    {
        $config = [
            'modules' => [
                'enabled' => [
                        ['\Codeception\Lib\PartedModule' => [
                            'part' => 'one'
                        ]
                    ]
                ],
            ]
        ];
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), $config);
        $this->moduleContainer->create('\Codeception\Lib\PartedModule');
        $actions = $this->moduleContainer->getActions();
        $this->assertArrayHasKey('partOne', $actions);
        $this->assertArrayNotHasKey('partTwo', $actions);
    }

    public function testShortConfigFormat()
    {
        $config = [
            'modules' =>
                ['enabled' => [
                    ['Codeception\Lib\StubModule' => [
                        'firstField' => 'firstValue',
                        'secondField' => 'secondValue',
                        ]
                    ]
                ]
            ]
        ];

        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), $config);
        $module = $this->moduleContainer->create('Codeception\Lib\StubModule');

        $this->assertEquals('firstValue', $module->_getFirstField());
        $this->assertEquals('secondValue', $module->_getSecondField());
    }

    public function testShortConfigDependencies()
    {
        $config = ['modules' => [
            'enabled' => [['Codeception\Lib\DependencyModule' => [
                'depends' => 'Codeception\Lib\ConflictedModule'
            ]]],
        ]];
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), $config);
        $this->moduleContainer->create('Codeception\Lib\DependencyModule');
        $this->moduleContainer->hasModule('\Codeception\Lib\DependencyModule');
    }

    public function testInjectModuleIntoHelper()
    {
        $config = ['modules' => [
            'enabled' => ['Codeception\Lib\HelperModule'],
        ]];
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), $config);
        $this->moduleContainer->create('Codeception\Lib\HelperModule');
        $this->moduleContainer->hasModule('Codeception\Lib\HelperModule');
    }
}

class StubModule extends \Codeception\Module
{
    protected $requiredFields = [
        'firstField',
        'secondField',
    ];

    public function _getFirstField()
    {
        return $this->config['firstField'];
    }

    public function _getSecondField()
    {
        return $this->config['secondField'];
    }
}




class HelperModule extends \Codeception\Module
{
    public function _inject(ConflictedModule $module)
    {
        $this->module = $module;
    }
}

class ConflictedModule extends \Codeception\Module implements ConflictsWithModule
{
    public function _conflicts()
    {
        return 'Cli';
    }
}

class ConflictedModule2 extends \Codeception\Module implements ConflictsWithModule
{
    public function _conflicts()
    {
        return '\Codeception\Module\Cli';
    }
}

class ConflictedModule3 extends \Codeception\Module implements ConflictsWithModule
{
    public function _conflicts()
    {
        return 'Codeception\Lib\Interfaces\Web';
    }
}

class DependencyModule extends \Codeception\Module implements DependsOnModule
{
    public function _depends()
    {
        return ['Codeception\Lib\ConflictedModule' => 'Error message'];
    }

    public function _inject()
    {
    }
}

class PartedModule extends \Codeception\Module implements \Codeception\Lib\Interfaces\PartedModule
{
    public function _parts()
    {
        return ['one'];
    }

    /**
     * @part one
     */
    public function partOne()
    {
    }

    /**
     * @part two
     */
    public function partTwo()
    {
    }
}
