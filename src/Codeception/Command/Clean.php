<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Util\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Cleans `output` directory
 *
 * * `codecept clean`
 * * `codecept clean -c path/to/project`
 *
 */
class Clean extends Command
{
    use Shared\Config;

    public function getDescription()
    {
        return 'Cleans or creates _output directory';
    }

    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getGlobalConfig($input->getOption('config'));
        $output->writeln("<info>Cleaning up " . Configuration::outputDir() . "...</info>");
        FileSystem::doEmptyDir(Configuration::outputDir());
        $output->writeln("Done");
    }
}
