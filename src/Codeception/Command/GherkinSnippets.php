<?php
namespace Codeception\Command;

use Codeception\Lib\Generator\GherkinSnippets as SnippetsGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates code snippets for matched feature files in a suite.
 * Code snuppets are expected to be implemtned in Actor or PageOjects
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
    use Shared\Config;
    use Shared\Style;

    protected function configure()
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

    public function getDescription()
    {
        return 'Fetches empty steps from feature files of suite and prints code snippets for them';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addStyles($output);
        $suite = $input->getArgument('suite');
        $test = $input->getArgument('test');
        $config = $this->getSuiteConfig($suite);

        $generator = new SnippetsGenerator($config, $test);
        $snippets = $generator->getSnippets();
        $features = $generator->getFeatures();

        if (empty($snippets)) {
            $output->writeln("<notice> All Gherkin steps are defined. Exiting... </notice>");
            return;
        }
        $output->writeln("<comment> Snippets found in: </comment>");
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
    }
}
