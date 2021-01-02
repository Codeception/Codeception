<?php

declare(strict_types=1);

namespace Codeception\Command;

use Stecman\Component\Symfony\Console\BashCompletion\Completion\ShellPathCompletion;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompletionFallback extends Command
{
    protected function configure()
    {
        $this
            ->setName('_completion')
            ->setDescription('BASH completion hook.')
            ->setHelp(<<<END
To enable BASH completion, install optional stecman/symfony-console-completion first:

    <comment>composer require stecman/symfony-console-completion</comment>

END
            );

        // Hide this command from listing
        $this->setHidden(true);
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Install optional <comment>stecman/symfony-console-completion</comment>");
        return 0;
    }
}
