<?php
namespace Codeception\Command;

use Codeception\Lib\Generator\PhpUnit as PhpUnitGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates skeleton for unit test as in classical PHPUnit.
 *
 * * `codecept g:phpunit unit UserTest`
 * * `codecept g:phpunit unit User`
 * * `codecept g:phpunit unit "App\User`
 *
 */
class GeneratePhpUnit extends Command
{
    use Shared\FileSystem;
    use Shared\Config;

    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('suite', InputArgument::REQUIRED, 'suite where tests will be put'),
            new InputArgument('class', InputArgument::REQUIRED, 'class name'),
        ]);
        parent::configure();
    }

    public function getDescription()
    {
        return 'Generates empty PHPUnit test without Codeception additions';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $class = $input->getArgument('class');

        $config = $this->getSuiteConfig($suite, $input->getOption('config'));

        $path = $this->buildPath($config['path'], $class);

        $filename = $this->completeSuffix($this->getClassName($class), 'Test');
        $filename = $path . $filename;

        $gen = new PhpUnitGenerator($config, $class);

        $res = $this->save($filename, $gen->produce());
        if (!$res) {
            $output->writeln("<error>Test $filename already exists</error>");
            exit;
        }

        $output->writeln("<info>Test was created in $filename</info>");
    }
}
