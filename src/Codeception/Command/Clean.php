<?php
namespace Codeception\Command;


use Codeception\Configuration;
use Codeception\Util\FileSystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Clean extends Base {

    public function getDescription() {
        return 'Cleans _log directory';
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
        $conf = $this->getGlobalConfig($input->getOption('config'));

        $output->writeln("<info>Cleaning up ".Configuration::logDir()."...</info>");
        FileSystem::doEmptyDir(Configuration::logDir());
        $output->writeln("Done");
    }
}