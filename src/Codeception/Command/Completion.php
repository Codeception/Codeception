<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Configuration;
use Stecman\Component\Symfony\Console\BashCompletion\Completion as ConsoleCompletion;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionInterface as ConsoleCompletionInterface;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\ShellPathCompletion;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition as SymfonyInputDefinition;
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
    protected function configureCompletion(CompletionHandler $handler): void
    {
        // Can't set for all commands, because it wouldn't work well with generate:suite
        $suiteCommands = [
            'run',
            'config:validate',
            'console',
            'dry-run',
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
                ConsoleCompletionInterface::TYPE_ARGUMENT,
                Configuration::suites()
            ));
        }

        $handler->addHandlers([
            new ShellPathCompletion(
                ConsoleCompletionInterface::ALL_COMMANDS,
                'path',
                ConsoleCompletionInterface::TYPE_ARGUMENT
            ),
            new ShellPathCompletion(
                ConsoleCompletionInterface::ALL_COMMANDS,
                'test',
                ConsoleCompletionInterface::TYPE_ARGUMENT
            ),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('generate-hook') && $input->getOption('use-vendor-bin')) {
            global $argv;
            $argv[0] = 'vendor/bin/' . basename($argv[0]);
        }

        parent::execute($input, $output);
        return Command::SUCCESS;
    }

    protected function createDefinition(): SymfonyInputDefinition
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
