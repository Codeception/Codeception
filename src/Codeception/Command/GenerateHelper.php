<?php
namespace Codeception\Command;

use Codeception\Lib\Generator\Helper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Creates empty Helper class.
 *
 * * `codecept g:helper MyHelper`
 *
 */
class GenerateHelper extends Command
{
    use Shared\FileSystem;
    use Shared\Config;

    protected function configure()
    {
        $this->setDefinition(
            array(
                new InputArgument('name', InputArgument::REQUIRED, 'suite to be generated'),
                new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
            )
        );
    }

    public function getDescription()
    {
        return 'Generates new helper';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = ucfirst($input->getArgument('name'));
        $config = \Codeception\Configuration::config($input->getOption('config'));
        $file = \Codeception\Configuration::helpersDir() . "{$name}Helper.php";

        $res = $this->save($file, (new Helper($name, $config['namespace']))->produce());
        if ($res) {
            $output->writeln("<info>Helper $file created</info>");
        } else {
            $output->writeln("<error>Error creating helper $file</error>");
        }
    }


}