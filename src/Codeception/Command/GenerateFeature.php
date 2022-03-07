<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Lib\Generator\Feature;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function basename;
use function preg_match;
use function rtrim;

/**
 * Generates Feature file (in Gherkin):
 *
 * * `codecept generate:feature suite Login`
 * * `codecept g:feature suite subdir/subdir/login.feature`
 * * `codecept g:feature suite login.feature -c path/to/project`
 *
 */
class GenerateFeature extends Command
{
    use Shared\FileSystemTrait;
    use Shared\ConfigTrait;

    protected function configure(): void
    {
        $this->setDefinition([
            new InputArgument('suite', InputArgument::REQUIRED, 'suite to be tested'),
            new InputArgument('feature', InputArgument::REQUIRED, 'feature to be generated'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ]);
    }

    public function getDescription(): string
    {
        return 'Generates empty feature file in suite';
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $suite = $input->getArgument('suite');
        $filename = $input->getArgument('feature');

        $config = $this->getSuiteConfig($suite);
        $this->createDirectoryFor($config['path'], $filename);

        $feature = new Feature(basename($filename));
        if (!preg_match('#\.feature$#', $filename)) {
            $filename .= '.feature';
        }
        $fullPath = rtrim($config['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        $res = $this->createFile($fullPath, $feature->produce());
        if (!$res) {
            $output->writeln("<error>Feature {$filename} already exists</error>");
            return 1;
        }
        $output->writeln("<info>Feature was created in {$fullPath}</info>");
        return 0;
    }
}
