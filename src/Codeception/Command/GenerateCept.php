<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Lib\Generator\Cept;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function rtrim;

/**
 * @deprecated
 */
class GenerateCept extends Command
{
    use Shared\FileSystemTrait;
    use Shared\ConfigTrait;

    protected function configure(): void
    {
        $this->setDefinition([
            new InputArgument('suite', InputArgument::REQUIRED, 'suite to be tested'),
            new InputArgument('test', InputArgument::REQUIRED, 'test to be run'),
        ]);
    }

    public function getDescription(): string
    {
        return 'Generates empty Cept file in suite';
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $suite = $input->getArgument('suite');
        $filename = $input->getArgument('test');

        $config = $this->getSuiteConfig($suite);
        $this->createDirectoryFor($config['path'], $filename);

        $filename = $this->completeSuffix($filename, 'Cept');
        $cept = new Cept($config);

        $fullPath = rtrim($config['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        $res = $this->createFile($fullPath, $cept->produce());
        if (!$res) {
            $output->writeln("<error>Test {$filename} already exists</error>");
            return Command::FAILURE;
        }
        $output->writeln("<info>Test was created in {$fullPath}</info>");
        return Command::SUCCESS;
    }
}
