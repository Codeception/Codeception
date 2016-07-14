<?php
namespace Codeception\Command;

use Codeception\Lib\Generator\Feature;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates Feature file (in Gherkin):
 *
 * * `codecept generate:feature suite Login`
 * * `codecept g:feature suite subdir/subdir/login.feature`
 * * `codecept g:feature suite login.feature -c path/to/project`
 *
 */
class GenerateFeature extends Command
{
    use Shared\FileSystem;
    use Shared\Config;

    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('suite', InputArgument::REQUIRED, 'suite to be tested'),
            new InputArgument('feature', InputArgument::REQUIRED, 'feature to be generated'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ]);
    }

    public function getDescription()
    {
        return 'Generates empty feature file in suite';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $filename = $input->getArgument('feature');

        $config = $this->getSuiteConfig($suite, $input->getOption('config'));
        $this->buildPath($config['path'], $filename);

        $gen = new Feature(basename($filename));
        if (!preg_match('~\.feature$~', $filename)) {
            $filename .= '.feature';
        }
        $full_path = rtrim($config['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        $res = $this->save($full_path, $gen->produce());
        if (!$res) {
            $output->writeln("<error>Feature $filename already exists</error>");
            return;
        }
        $output->writeln("<info>Feature was created in $full_path</info>");
    }
}
