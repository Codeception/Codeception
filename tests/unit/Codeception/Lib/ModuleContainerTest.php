<?php

declare(strict_types=1);

namespace Codeception\Lib;

use Codeception\Exception\ModuleException;
use Codeception\Lib\Interfaces\ConflictsWithModule;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Test\Unit;
use Codeception\Stub;

// @codingStandardsIgnoreFile
class ModuleContainerTest extends Unit
{

    /**
     * @var ModuleContainer
     */
    protected $moduleContainer;

    protected function _setUp(): void
    {
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), []);
    }

    protected function _tearDown(): void
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
    public function testCreateModule(): void
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
    public function testActions(): void
    {
        $this->moduleContainer->create('EmulateModuleHelper');
        $actions = $this->moduleContainer->getActions();
        $this->assertArrayHasKey('seeEquals', $actions);
        $this->assertEquals('EmulateModuleHelper', $actions['seeEquals']);
    }

    /**
     * @group core
     */
    public function testActionsInExtendedModule(): void
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
    public function testActionsInExtendedButNotInheritedModule(): void
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
    public function testExplicitlySetActionsOnNotInherited(): void
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
    public function testActionsExplicitlySetForNotInheritedModule(): void
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
    public function testCreateModuleWithoutRequiredFields(): void
    {
        $this->expectException('\Codeception\Exception\ModuleConfigException');
        $this->moduleContainer->create('Codeception\Lib\StubModule');
    }

    /**
     * @group core
     */
    public function testCreateModuleWithCorrectConfig(): void
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
    public function testReconfigureModule(): void
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

    public function testConflictsByModuleName(): void
    {
        $this->expectException('Codeception\Exception\ModuleConflictException');
        $this->moduleContainer->create('Codeception\Lib\ConflictedModule');
        $this->moduleContainer->create('Cli');
        $this->moduleContainer->validateConflicts();
    }


    public function testConflictsByClass(): void
    {
        $this->expectException('Codeception\Exception\ModuleConflictException');
        $this->moduleContainer->create('Codeception\Lib\ConflictedModule2');
        $this->moduleContainer->create('Cli');
        $this->moduleContainer->validateConflicts();
    }

    public function testConflictsByInterface(): void
    {
        $this->markTestSkipped('This test uses modules that aren\'t loaded for core tests');

        $this->expectException('Codeception\Exception\ModuleConflictException');
        $this->moduleContainer->create('Codeception\Lib\ConflictedModule3');
        $this->moduleContainer->create('Symfony2');
        $this->moduleContainer->validateConflicts();
    }

    public function testConflictsByWebInterface(): void
    {
        $this->markTestSkipped('This test uses modules that aren\'t loaded for core tests');

        $this->expectException('Codeception\Exception\ModuleConflictException');
        $this->moduleContainer->create('Laravel5');
        $this->moduleContainer->create('Symfony2');
        $this->moduleContainer->validateConflicts();
    }

    public function testConflictsForREST(): void
    {
        $this->markTestSkipped('This test uses modules that aren\'t loaded for core tests');

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

    public function testConflictsOnDependentModules(): void
    {
        $this->markTestSkipped('This test uses modules that aren\'t loaded for core tests');

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

    public function testNoConflictsForPartedModules(): void
    {
        $this->markTestSkipped('This test uses modules that aren\'t loaded for core tests');

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

    public function testModuleDependenciesFail(): void
    {
        $this->expectException('Codeception\Exception\ModuleRequireException');
        $this->moduleContainer->create('Codeception\Lib\DependencyModule');
    }

    public function testModuleDependencies(): void
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

    public function testModuleParts1(): void
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

    public function testModuleParts2(): void
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

    public function testShortConfigParts(): void
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

    public function testShortConfigFormat(): void
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

    public function testShortConfigDependencies(): void
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

    public function testInjectModuleIntoHelper(): void
    {
        $config = ['modules' => [
            'enabled' => ['Codeception\Lib\HelperModule'],
        ]];
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), $config);
        $this->moduleContainer->create('Codeception\Lib\HelperModule');
        $this->moduleContainer->hasModule('Codeception\Lib\HelperModule');
    }

    public function testSuggestMissingModule(): void
    {
        $correctModule = 'Codeception\Lib\HelperModule';
        $wrongModule = 'Codeception\Lib\Helpamodule';

        $config = ['modules' => [
            'enabled' => [$correctModule],
        ]];
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), $config);
        $this->moduleContainer->create('Codeception\Lib\HelperModule');

        $message = "Codeception\Lib\ModuleContainer: Module $wrongModule couldn't be connected (did you mean '$correctModule'?)";
        $this->expectException('\Codeception\Exception\ModuleException');
        $this->expectExceptionMessage($message);
        $this->moduleContainer->getModule($wrongModule);
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
    public function _inject(ConflictedModule $module): void
    {
        $this->module = $module;
    }
}

class ConflictedModule extends \Codeception\Module implements ConflictsWithModule
{
    public function _conflicts(): string
    {
        return 'Cli';
    }
}

class ConflictedModule2 extends \Codeception\Module implements ConflictsWithModule
{
    public function _conflicts(): string
    {
        return '\Codeception\Module\Cli';
    }
}

class ConflictedModule3 extends \Codeception\Module implements ConflictsWithModule
{
    public function _conflicts(): string
    {
        return 'Codeception\Lib\Interfaces\Web';
    }
}

class DependencyModule extends \Codeception\Module implements DependsOnModule
{
    public function _depends(): array
    {
        return ['Codeception\Lib\ConflictedModule' => 'Error message'];
    }

    public function _inject(): void
    {
    }
}

class PartedModule extends \Codeception\Module implements \Codeception\Lib\Interfaces\PartedModule
{
    public function _parts(): array
    {
        return ['one'];
    }

    /**
     * @part one
     */
    public function partOne(): void
    {
    }

    /**
     * @part two
     */
    public function partTwo(): void
    {
    }
}
