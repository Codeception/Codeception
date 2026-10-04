<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->skipPath(__DIR__ . '/tests/Support')
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())
    ->layer('Attribute', 'src/Codeception/Attribute')
    ->layer('Exception', 'src/Codeception/Exception')
    ->layer('PHPUnit', 'src/PHPUnit')
    ->layer('Console', 'src/Codeception/Lib/Console')
    ->layer('Interfaces', 'src/Codeception/Lib/Interfaces')
    ->layer('Connector', 'src/Codeception/Lib/Connector')
    ->layer('Generator', 'src/Codeception/Lib/Generator')
    ->layer('Actor', ['src/Codeception/Actor.php', 'src/Codeception/Lib/Actor'])
    ->layer('Lib', [
        'src/Codeception/Configuration.php',
        'src/Codeception/Module.php',
        'src/Codeception/Snapshot.php',
        'src/Codeception/Lib',
    ], [
        'src/Codeception/Lib/Actor',
        'src/Codeception/Lib/Connector',
        'src/Codeception/Lib/Console',
        'src/Codeception/Lib/Generator',
        'src/Codeception/Lib/Interfaces',
    ])
    ->layer('Util', 'src/Codeception/Util')
    ->layer('StepArgument', 'src/Codeception/Step/Argument')
    ->layer('Step', ['src/Codeception/Step.php', 'src/Codeception/Step'], 'src/Codeception/Step/Argument')
    ->layer('Event', ['src/Codeception/Events.php', 'src/Codeception/Event'])
    ->layer('TestInterfaces', 'src/Codeception/Test/Interfaces')
    ->layer('TestFeature', 'src/Codeception/Test/Feature')
    ->layer('TestLoader', ['src/Codeception/Test/Loader.php', 'src/Codeception/Test/Loader'])
    ->layer('Test', [
        'src/Codeception/Example.php',
        'src/Codeception/ResultAggregator.php',
        'src/Codeception/Scenario.php',
        'src/Codeception/TestInterface.php',
        'src/Codeception/Test',
    ], [
        'src/Codeception/Test/Feature',
        'src/Codeception/Test/Interfaces',
        'src/Codeception/Test/Loader.php',
        'src/Codeception/Test/Loader',
    ])
    ->layer('Suite', ['src/Codeception/Suite.php', 'src/Codeception/SuiteManager.php'])
    ->layer('Extension', ['src/Codeception/Extension.php', 'src/Codeception/GroupObject.php'])
    ->layer('Subscriber', 'src/Codeception/Subscriber')
    ->layer('CoverageSubscriber', 'src/Codeception/Coverage/Subscriber')
    ->layer('Coverage', 'src/Codeception/Coverage', 'src/Codeception/Coverage/Subscriber')
    ->layer('Reporter', 'src/Codeception/Reporter')
    ->layer('Template', ['src/Codeception/InitTemplate.php', 'src/Codeception/Template'])
    ->layer('Command', [
        'src/Codeception/Application.php',
        'src/Codeception/Codecept.php',
        'src/Codeception/CustomCommandInterface.php',
        'src/Codeception/Command',
    ])
    ->ruleset([
        'Attribute'          => [],
        'Exception'          => [],
        'PHPUnit'            => [],
        'Console'            => [],
        'Interfaces'         => [],
        'Connector'          => [],
        'Generator'          => ['Actor', 'Command', 'Exception', 'Lib', 'Step', 'TestLoader', 'Util'],
        'Actor'              => ['Command', 'Lib', 'Step', 'Test', 'Util'],
        'Lib'                => ['Actor', 'Exception', 'Interfaces', 'Step', 'Test', 'Util'],
        'Util'               => ['Console', 'Lib', 'Step'],
        'StepArgument'       => [],
        'Step'               => ['Actor', 'Exception', 'Lib', 'StepArgument', 'Test', 'Util'],
        'Event'              => ['Step', 'Suite', 'Test'],
        'TestInterfaces'     => ['Test'],
        'TestFeature'        => ['Coverage', 'Event', 'Lib', '+TestInterfaces'],
        'TestLoader'         => ['Command', 'Exception', 'Generator', 'Lib', 'Test', 'Util'],
        'Test'               => ['Actor', 'Exception', 'Generator', 'PHPUnit', '+TestFeature', '+Util'],
        'Suite'              => ['Command', 'Event', 'Exception', 'Lib', '+TestInterfaces', 'TestLoader'],
        'Extension'          => ['Console', 'Event', 'Exception', 'Lib'],
        'Subscriber'         => ['+Event', 'Exception', 'Generator', '+TestInterfaces', '+Util'],
        'CoverageSubscriber' => ['Console', '+Coverage', 'Event', 'Util'],
        'Coverage'           => ['CoverageSubscriber', 'Exception', 'Lib', 'Subscriber'],
        'Reporter'           => ['Console', 'Event', 'Interfaces', 'Step', 'Subscriber', '+TestInterfaces', 'Util'],
        'Template'           => ['Command', 'Generator', 'Lib', 'Util'],
        'Command'            => ['Console', 'CoverageSubscriber', 'Interfaces', 'Reporter', 'Subscriber', '+Suite', '+Template'],
    ]);
