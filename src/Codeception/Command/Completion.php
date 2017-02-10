<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Stecman\Component\Symfony\Console\BashCompletion\Completion as ConsoleCompletion;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionHandler;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\ShellPathCompletion as ShellPathCompletion;

class Completion extends CompletionCommand
{
    protected function configureCompletion(CompletionHandler $handler)
    {
        // Can't set for all commands, because it wouldn't work well with generate:suite
        $suiteCommands = [
            'run',
            'config:validate',
            'console',
            'dry-run',
            'generate:cept',
            'generate:cest',
            'generate:feature',
            'generate:phpunit',
            'generate:scenarios',
            'generate:stepobject',
            'generate:test',
            'gherkin:snippets',
            'gherkin:steps'
        ];

        foreach ($suiteCommands as $suiteCommand) {
            $handler->addHandler(new ConsoleCompletion(
                $suiteCommand,
                'suite',
                ConsoleCompletion::TYPE_ARGUMENT,
                Configuration::suites()
            ));
        }

        $handler->addHandlers([
            new ShellPathCompletion(
                ConsoleCompletion::ALL_COMMANDS,
                'path',
                ConsoleCompletion::TYPE_ARGUMENT
            ),
            new ShellPathCompletion(
                ConsoleCompletion::ALL_COMMANDS,
                'test',
                ConsoleCompletion::TYPE_ARGUMENT
            ),
        ]);
    }
}
