<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Template\Bootstrap as BootstrapTemplate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates default config, tests directory and sample suites for current project.
 * Use this command to start building a test suite.
 *
 * By default, it will create 3 suites **Acceptance**, **Functional**, and **Unit**.
 *
 * * `codecept bootstrap` - creates `tests` dir and `codeception.yml` in current dir.
 * * `codecept bootstrap --empty` - creates `tests` dir without suites
 * * `codecept bootstrap --namespace Frontend` - creates tests, and use `Frontend` namespace for actor classes and helpers.
 * * `codecept bootstrap --actor Wizard` - sets actor as Wizard, to have `TestWizard` actor in tests.
 * * `codecept bootstrap path/to/the/project` - provide different path to a project, where tests should be placed
 *
 */
#[AsCommand(
    name: 'bootstrap',
    description: 'Creates default test suites and generates all required files'
)]
class Bootstrap extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::OPTIONAL, 'custom installation dir')
            ->addOption('namespace', 's', InputOption::VALUE_OPTIONAL, 'Namespace to add for actor classes and helpers')
            ->addOption('actor', 'a', InputOption::VALUE_OPTIONAL, 'Custom actor instead of Tester')
            ->addOption('empty', 'e', InputOption::VALUE_NONE, "Don't create standard suites");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $bootstrap = new BootstrapTemplate($input, $output);
        if ($path = $input->getArgument('path')) {
            $bootstrap->initDir($path);
        }

        $bootstrap->setup();
        return Command::SUCCESS;
    }
}
