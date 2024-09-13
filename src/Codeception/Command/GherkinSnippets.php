<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Lib\Generator\GherkinSnippets as GherkinSnippetsGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function count;

/**
 * Generates code snippets for matched feature files in a suite.
 * Code snippets are expected to be implemented in Actor or PageObjects
 *
 * Usage:
 *
 * * `codecept gherkin:snippets acceptance` - snippets from all feature of acceptance tests
 * * `codecept gherkin:snippets acceptance/feature/users` - snippets from `feature/users` dir of acceptance tests
 * * `codecept gherkin:snippets acceptance user_account.feature` - snippets from a single feature file
 * * `codecept gherkin:snippets acceptance/feature/users/user_accout.feature` - snippets from feature file in a dir
 */
class GherkinSnippets extends Command
{
    use Shared\ConfigTrait;
    use Shared\StyleTrait;

    protected function configure(): void
    {
        $this->setDescription('Fetches empty steps from feature files of suite and prints code snippets for them')
            ->addArgument('suite', InputArgument::REQUIRED, 'Suite to scan for feature files')
            ->addArgument('test', InputArgument::OPTIONAL, 'Test to be scanned')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->addStyles($output);
        $suite = $input->getArgument('suite');
        $test = $input->getArgument('test');
        $config = $this->getSuiteConfig($suite);

        $generator = new GherkinSnippetsGenerator($config, $test);
        $snippets = $generator->getSnippets();
        if ($snippets === []) {
            $output->writeln("<notice> All Gherkin steps are defined. Exiting... </notice>");
            return Command::SUCCESS;
        }
        $output->writeln("<comment> Snippets found in: </comment>");

        foreach ($generator->getFeatures() as $feature) {
            $output->writeln("<info>  - {$feature} </info>");
        }
        $output->writeln("<comment> Generated Snippets: </comment>");
        $output->writeln("<info> ----------------------------------------- </info>");
        foreach ($snippets as $snippet) {
            $output->writeln($snippet);
        }
        $output->writeln("<info> ----------------------------------------- </info>");
        $output->writeln(sprintf(' <bold>%d</bold> snippets proposed', count($snippets)));
        $output->writeln("<notice> Copy generated snippets to {$config['actor']} or a specific Gherkin context </notice>");
        return Command::SUCCESS;
    }
}
