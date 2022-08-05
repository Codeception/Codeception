<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Stecman\Component\Symfony\Console\BashCompletion\Completion as ConsoleCompletion;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionHandler;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\ShellPathCompletion;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// phpcs:ignoreFile PSR1.Files.SideEffects.FoundWithSymbols
if (!class_exists(ConsoleCompletion::class)) {
    echo "Please install `stecman/symfony-console-completion\n` to enable auto completion";
    return;
}

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('generate-hook') && $input->getOption('use-vendor-bin')) {
            global $argv;
            $argv[0] = 'vendor/bin/' . basename($argv[0]);
        }

        parent::execute($input, $output);
        return 0;
    }

    protected function createDefinition()
    {
        $definition = parent::createDefinition();
        $definition->addOption(new InputOption(
            'use-vendor-bin',
            null,
            InputOption::VALUE_NONE,
            'Use the vendor bin for autocompletion.'
        ));

        return $definition;
    }
}
