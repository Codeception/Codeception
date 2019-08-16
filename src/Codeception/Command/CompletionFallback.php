<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Stecman\Component\Symfony\Console\BashCompletion\Completion as ConsoleCompletion;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionHandler;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\ShellPathCompletion;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

        // Hide this command from listing if supported
        // Command::setHidden() was not available before Symfony 3.2.0
        if (method_exists($this, 'setHidden')) {
            $this->setHidden(true);
        }
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Install optional <comment>stecman/symfony-console-completion</comment>");
    }
}
