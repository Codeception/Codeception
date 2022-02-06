<?php

declare(strict_types=1);

namespace Codeception\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompletionFallback extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = '_completion';

    protected function configure(): void
    {
        $this
            ->setDescription('BASH completion hook.')
            ->setHidden(true) // Hide from listing
            ->setHelp(<<<END
To enable BASH completion, install optional stecman/symfony-console-completion first:

    <comment>composer require stecman/symfony-console-completion</comment>

END);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Install optional <comment>stecman/symfony-console-completion</comment>");
        return 0;
    }
}
