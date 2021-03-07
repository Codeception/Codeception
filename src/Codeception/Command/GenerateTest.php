<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Lib\Generator\Test as TestGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates skeleton for Unit Test that extends `Codeception\TestCase\Test`.
 *
 * * `codecept g:test unit User`
 * * `codecept g:test unit "App\User"`
 */
class GenerateTest extends Command
{
    use Shared\FileSystemTrait;
    use Shared\ConfigTrait;

    protected function configure(): void
    {
        $this->setDefinition(
            [
                new InputArgument('suite', InputArgument::REQUIRED, 'suite where tests will be put'),
                new InputArgument('class', InputArgument::REQUIRED, 'class name'),
            ]
        );
        parent::configure();
    }

    public function getDescription(): string
    {
        return 'Generates empty unit test file in suite';
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $suite = $input->getArgument('suite');
        $class = $input->getArgument('class');

        $config = $this->getSuiteConfig($suite);

        $className = $this->getShortClassName($class);
        $path = $this->createDirectoryFor($config['path'], $class);

        $filename = $this->completeSuffix($className, 'Test');
        $filename = $path . $filename;

        $test = new TestGenerator($config, $class);

        $res = $this->createFile($filename, $test->produce());

        if (!$res) {
            $output->writeln("<error>Test {$filename} already exists</error>");
            return 1;
        }
        $output->writeln("<info>Test was created in {$filename}</info>");
        return 0;
    }
}
