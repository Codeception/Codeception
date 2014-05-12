<?php
namespace Codeception\Command;


use Codeception\Configuration;
use Codeception\Util\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Cleans `log` directory
 * `codecept clean`
 * `codecept clean -c path/to/project`
 *
 */
class Clean extends Command
{
    use Shared\Config;

    public function getDescription() {
        return 'Cleans or creates _log directory';
    }

    protected function configure()
    {
        $this->setDefinition(array(
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ));
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getGlobalConfig($input->getOption('config'));

        $output->writeln("<info>Cleaning up ".Configuration::logDir()."...</info>");
        if (!file_exists(Configuration::logDir())) {
            mkdir(Configuration::logDir(), 0777, true);
        } else {
            chmod(Configuration::logDir(), 0777);
        }
        FileSystem::doEmptyDir(Configuration::logDir());
        $output->writeln("Done");
    }
}