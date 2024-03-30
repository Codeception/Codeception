<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Lib\Generator\Snapshot as SnapshotGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function ucfirst;

/**
 * Generates Snapshot.
 * Snapshot can be used to test dynamical data.
 * If suite name is provided, an actor class will be included into placeholder
 *
 * * `codecept g:snapshot UserEmails`
 * * `codecept g:snapshot Products`
 * * `codecept g:snapshot acceptance UserEmails`
 */
class GenerateSnapshot extends Command
{
    use Shared\FileSystemTrait;
    use Shared\ConfigTrait;

    protected function configure(): void
    {
        $this->setDescription('Generates empty Snapshot class')
            ->addArgument('suite', InputArgument::REQUIRED, 'Suite name or snapshot name)')
            ->addArgument('snapshot', InputArgument::OPTIONAL, 'Name of snapshot');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $suite = (string)$input->getArgument('suite');
        $class = $input->getArgument('snapshot');

        if (!$class) {
            $class = $suite;
            $suite = '';
        }

        $conf = $suite
            ? $this->getSuiteConfig($suite)
            : $this->getGlobalConfig();

        if ($suite) {
            $suite = DIRECTORY_SEPARATOR . ucfirst($suite);
        }

        $path = $this->createDirectoryFor(Configuration::supportDir() . 'Snapshot' . $suite, $class);

        $filename = $path . $this->getShortClassName($class) . '.php';

        $output->writeln($filename);

        $snapshot = new SnapshotGenerator($conf, ucfirst($suite) . '\\' . $class);
        $res = $this->createFile($filename, $snapshot->produce());

        if (!$res) {
            $output->writeln("<error>Snapshot {$filename} already exists</error>");
            return Command::FAILURE;
        }
        $output->writeln("<info>Snapshot was created in {$filename}</info>");
        return Command::SUCCESS;
    }
}
