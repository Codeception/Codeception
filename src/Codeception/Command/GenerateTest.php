<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Lib\Generator\Test as TestGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
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
#[AsCommand(
    name: 'generate:test',
    description: 'Generates empty unit test file in suite'
)]
class GenerateTest extends Command
{
    use Shared\FileSystemTrait;
    use Shared\ConfigTrait;

    protected function configure(): void
    {
        $this
            ->addArgument('suite', InputArgument::REQUIRED, 'Suite where tests will be put')
            ->addArgument('class', InputArgument::REQUIRED, 'Class name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $suite = $input->getArgument('suite');
        $class = $input->getArgument('class');

        $config = $this->getSuiteConfig($suite);

        $className = $this->getShortClassName($class);
        $path = $this->createDirectoryFor($config['path'], $class);
        $filename = $path . $this->completeSuffix($className, 'Test');

        $test = new TestGenerator($config, $class);

        $res = $this->createFile($filename, $test->produce());
        if (!$res) {
            $output->writeln("<error>Test {$filename} already exists</error>");
            return Command::FAILURE;
        }
        $output->writeln("<info>Test was created in {$filename}</info>");
        return Command::SUCCESS;
    }
}
