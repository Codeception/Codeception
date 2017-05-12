<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Lib\Generator\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates empty Helper class.
 *
 * * `codecept g:helper MyHelper`
 * * `codecept g:helper "My\Helper"`
 *
 */
class GenerateHelper extends Command
{
    use Shared\FileSystem;
    use Shared\Config;

    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'helper name'),
        ]);
    }

    public function getDescription()
    {
        return 'Generates new helper';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = ucfirst($input->getArgument('name'));
        $config = $this->getGlobalConfig();

        $path = $this->createDirectoryFor(Configuration::supportDir() . 'Helper', $name);
        $filename = $path . $this->getShortClassName($name) . '.php';

        $res = $this->createFile($filename, (new Helper($name, $config['namespace']))->produce());
        if ($res) {
            $output->writeln("<info>Helper $filename created</info>");
        } else {
            $output->writeln("<error>Error creating helper $filename</error>");
        }
    }
}
