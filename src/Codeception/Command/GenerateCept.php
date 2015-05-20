<?php
namespace Codeception\Command;

use Codeception\Lib\Generator\Cept;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates Cept (scenario-driven test) file:
 *
 * * `codecept generate:cept suite Login`
 * * `codecept g:cept suite subdir/subdir/testnameCept.php`
 * * `codecept g:cept suite LoginCept -c path/to/project`
 *
 */
class GenerateCept extends Command
{
    use Shared\FileSystem;
    use Shared\Config;

    protected function configure()
    {
        $this->setDefinition(array(
            new InputArgument('suite', InputArgument::REQUIRED, 'suite to be tested'),
            new InputArgument('test', InputArgument::REQUIRED, 'test to be run'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ));
    }

    public function getDescription() {
        return 'Generates empty Cept file in suite';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $filename = $input->getArgument('test');

        $config = $this->getSuiteConfig($suite, $input->getOption('config'));
        $this->buildPath($config['path'], $filename);

        $filename = $this->completeSuffix($filename, 'Cept');
        $gen = new Cept($config);

        $res = $this->save($config['path'].DIRECTORY_SEPARATOR . $filename, $gen->produce());
        if (!$res) {
            $output->writeln("<error>Test $filename already exists</error>");
            return;
        }
        $output->writeln("<info>Test was created in $filename</info>");
    }

}