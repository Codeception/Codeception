<?php
namespace Codeception\Command;

use Codeception\Lib\Generator\Cept;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @deprecated
 */
class GenerateCept extends Command
{
    use Shared\FileSystem;
    use Shared\Config;

    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('suite', InputArgument::REQUIRED, 'suite to be tested'),
            new InputArgument('test', InputArgument::REQUIRED, 'test to be run'),
        ]);
    }

    public function getDescription()
    {
        return 'Generates empty Cept file in suite';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $filename = $input->getArgument('test');

        $config = $this->getSuiteConfig($suite);
        $this->createDirectoryFor($config['path'], $filename);

        $filename = $this->completeSuffix($filename, 'Cept');
        $gen = new Cept($config);

        $full_path = rtrim($config['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        $res = $this->createFile($full_path, $gen->produce());
        if (!$res) {
            $output->writeln("<error>Test $filename already exists</error>");
            return 1;
        }
        $output->writeln("<info>Test was created in $full_path</info>");
        return 0;
    }
}
