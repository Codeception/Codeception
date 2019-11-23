<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Lib\Generator\Snapshot as SnapshotGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
    use Shared\FileSystem;
    use Shared\Config;

    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('suite', InputArgument::REQUIRED, 'Suite name or snapshot name)'),
            new InputArgument('snapshot', InputArgument::OPTIONAL, 'Name of snapshot'),
        ]);
        parent::configure();
    }

    public function getDescription()
    {
        return 'Generates empty Snapshot class';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $class = $input->getArgument('snapshot');

        if (!$class) {
            $class = $suite;
            $suite = null;
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

        $gen = new SnapshotGenerator($conf, ucfirst($suite) . '\\' . $class);
        $res = $this->createFile($filename, $gen->produce());

        if (!$res) {
            $output->writeln("<error>Snapshot $filename already exists</error>");
            return 1;
        }
        $output->writeln("<info>Snapshot was created in $filename</info>");
        return 0;
    }
}
