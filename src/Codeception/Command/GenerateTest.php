<?php
namespace Codeception\Command;

use Codeception\Lib\Generator\Test as TestGenerator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Generates skeleton for Unit Test that extends `Codeception\TestCase\Test`.
 *
 * * `codecept g:test unit User`
 * * `codecept g:test unit "App\User"`
 */
class GenerateTest extends Command
{
    use Shared\FileSystem;
    use Shared\Config;

    protected function configure()
    {
        $this->setDefinition(array(
            new InputArgument('suite', InputArgument::REQUIRED, 'suite where tests will be put'),
            new InputArgument('class', InputArgument::REQUIRED, 'class name'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ));
        parent::configure();
    }

    public function getDescription() {
        return 'Generates empty unit test file in suite';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $class = $input->getArgument('class');

        $config = $this->getSuiteConfig($suite, $input->getOption('config'));

        $className = $this->getClassName($class);
        $path = $this->buildPath($config['path'], $class);

        $filename = $this->completeSuffix($className, 'Test');
        $filename = $path.$filename;

        $gen = new TestGenerator($config, $class);

        $res = $this->save($filename, $gen->produce());

        if (!$res) {
            $output->writeln("<error>Test $filename already exists</error>");
            return;
        }
        $output->writeln("<info>Test was created in $filename</info>");

    }
}