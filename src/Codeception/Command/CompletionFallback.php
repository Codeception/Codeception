<?php

declare(strict_types=1);

namespace Codeception\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: '_completion',
    description: 'BASH completion hook.',
    hidden: true
)]
class CompletionFallback extends Command
{
    protected function configure(): void
    {
        $this->setHelp(<<<END
To enable BASH completion, install optional stecman/symfony-console-completion first:

    <comment>composer require stecman/symfony-console-completion</comment>

END);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Install optional <comment>stecman/symfony-console-completion</comment>");
        return Command::SUCCESS;
    }
}
