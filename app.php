<?php

require_once __DIR__ . '/autoload.php';

use Codeception\Application;

call_user_func(static function () {
    $app = new Application('Codeception', Codeception\Codecept::VERSION);

    $commands = [
        new Codeception\Command\Build('build'),
        new Codeception\Command\Run('run'),
        new Codeception\Command\Init('init'),
        new Codeception\Command\Console('console'),
        new Codeception\Command\Bootstrap('bootstrap'),
        new Codeception\Command\GenerateCest('generate:cest'),
        new Codeception\Command\GenerateTest('generate:test'),
        new Codeception\Command\GenerateSuite('generate:suite'),
        new Codeception\Command\GenerateHelper('generate:helper'),
        new Codeception\Command\GenerateScenarios('generate:scenarios'),
        new Codeception\Command\Clean('clean'),
        new Codeception\Command\GenerateGroup('generate:groupobject'),
        new Codeception\Command\GeneratePageObject('generate:pageobject'),
        new Codeception\Command\GenerateStepObject('generate:stepobject'),
        new Codeception\Command\GenerateSnapshot('generate:snapshot'),
        new Codeception\Command\GenerateEnvironment('generate:environment'),
        new Codeception\Command\GenerateFeature('generate:feature'),
        new Codeception\Command\GherkinSnippets('gherkin:snippets'),
        new Codeception\Command\GherkinSteps('gherkin:steps'),
        new Codeception\Command\DryRun('dry-run'),
        new Codeception\Command\ConfigValidate('config:validate'),
    ];

    // Suggests package
    if (class_exists('Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand')) {
        $commands[] = new Codeception\Command\Completion();
    } else {
        $commands[] = new Codeception\Command\CompletionFallback();
    }

    // addCommands() is available since symfony 7.4
    if (method_exists($app, 'addCommands')) {
        $app->addCommands($commands);
    } else {
        foreach ($commands as $command) {
            $app->add($command);
        }
    }

    $app->registerCustomCommands();

    // add only if within a phar archive.
    if ('phar:' === substr(__FILE__, 0, 5)) {
        $command = new Codeception\Command\SelfUpdate('self-update');
        // addCommand() is available since symfony 7.4
        if (method_exists($app, 'addCommand')) {
            $app->addCommand($command);
        } else {
            $app->add($command);
        }
    }

    $app->run();
});
