<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Util\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Recursively cleans `output` directory and generated code.
 *
 * * `codecept clean`
 *
 */
class Clean extends Command
{
    use Shared\Config;

    public function getDescription()
    {
        return 'Recursively cleans log and generated code';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectDir = Configuration::projectDir();
        $this->cleanProjectsRecursively($output, $projectDir);
        $output->writeln("Done");
    }

    private function cleanProjectsRecursively(OutputInterface $output, $projectDir)
    {
        $logDir = Configuration::logDir();
        $output->writeln("<info>Cleaning up output " . $logDir . "...</info>");
        FileSystem::doEmptyDir($logDir);

        $config = Configuration::config($projectDir);
        $subProjects = $config['include'];
        foreach ($subProjects as $subProject) {
            $subProjectDir = $projectDir . $subProject;
            $this->cleanProjectsRecursively($output, $subProjectDir);
        }
    }
}
