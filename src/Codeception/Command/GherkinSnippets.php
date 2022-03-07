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
        $this->setDefinition(
            [
                new InputArgument('suite', InputArgument::REQUIRED, 'suite to scan for feature files'),
                new InputArgument('test', InputArgument::OPTIONAL, 'test to be scanned'),
                new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
            ]
        );
        parent::configure();
    }

    public function getDescription(): string
    {
        return 'Fetches empty steps from feature files of suite and prints code snippets for them';
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->addStyles($output);
        $suite = $input->getArgument('suite');
        $test = $input->getArgument('test');
        $config = $this->getSuiteConfig($suite);

        $generator = new GherkinSnippetsGenerator($config, $test);

        $snippets = $generator->getSnippets();
        if (empty($snippets)) {
            $output->writeln("<notice> All Gherkin steps are defined. Exiting... </notice>");
            return 0;
        }
        $output->writeln("<comment> Snippets found in: </comment>");

        $features = $generator->getFeatures();
        foreach ($features as $feature) {
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
        return 0;
    }
}
