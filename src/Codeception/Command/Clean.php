<?php

declare(strict_types=1);

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
    use Shared\ConfigTrait;

    protected function configure(): void
    {
        $this->setDescription('Recursively cleans log and generated code');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cleanProjectsRecursively($output, Configuration::projectDir());
        $output->writeln("Done");
        return Command::SUCCESS;
    }

    private function cleanProjectsRecursively(OutputInterface $output, string $projectDir): void
    {
        $config = Configuration::config($projectDir);
        $logDir = Configuration::outputDir();
        $output->writeln(sprintf('<info>Cleaning up output %s...</info>', $logDir));
        FileSystem::doEmptyDir($logDir);

        $subProjects = $config['include'];
        foreach ($subProjects as $subProject) {
            $this->cleanProjectsRecursively($output, $projectDir . $subProject);
        }
    }
}
