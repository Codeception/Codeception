<?php
namespace Codeception\Command;

use Codeception\Lib\Generator\GherkinSnippets as SnippetsGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GherkinSnippets extends Command
{
    use Shared\Config;
    use Shared\Style;

    protected function configure()
    {
        $this->setDefinition(
            [
                new InputArgument('suite', InputArgument::REQUIRED, 'suite to scan for feature files'),
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
        $config = $this->getSuiteConfig($suite, $input->getOption('config'));

        $generator = new SnippetsGenerator($config);
        $snippets = $generator->getSnippets();

        if (empty($snippets)) {
            $output->writeln("<notice> All Gherkin steps are defined. Exiting... </notice>");
            return;
        }
        $output->writeln("<comment> Generated Snippets: </comment>");
        $output->writeln("<info> ----------------------------------------- </info>");
        foreach ($snippets as $snippet) {
            $output->writeln($snippet);
        }
        $output->writeln("<info> ----------------------------------------- </info>");
        $output->writeln("<notice> Copy generated snippets to {$config['class_name']} or a specific Gherkin context </notice>");
    }
}
