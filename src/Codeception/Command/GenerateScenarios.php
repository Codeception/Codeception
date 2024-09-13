<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use Codeception\SuiteManager;
use Codeception\Test\Cest;
use Codeception\Test\Interfaces\Descriptive;
use Codeception\Test\Interfaces\ScenarioDriven;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

use function basename;
use function file_exists;
use function is_writable;
use function mkdir;
use function preg_replace;

/**
 * Generates user-friendly text scenarios from scenario-driven tests (Cest).
 *
 * * `codecept g:scenarios acceptance` - for all acceptance tests
 * * `codecept g:scenarios acceptance --format html` - in html format
 * * `codecept g:scenarios acceptance --path doc` - generate scenarios to `doc` dir
 */
class GenerateScenarios extends Command
{
    use Shared\FileSystemTrait;
    use Shared\ConfigTrait;

    protected function configure(): void
    {
        $this->setDescription('Generates text representation for all scenarios')
            ->addArgument('suite', InputArgument::REQUIRED, 'suite from which texts should be generated')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Use specified path as destination instead of default')
            ->addOption('single-file', '', InputOption::VALUE_NONE, 'Render all scenarios to only one file')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Specify output format: html or text (default)', 'text');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $suite = $input->getArgument('suite');

        $suiteConf = $this->getSuiteConfig($suite);

        $path = $input->getOption('path') ?: Configuration::dataDir() . 'scenarios';

        $format = $input->getOption('format');

        @mkdir($path, 0777, true);

        if (!is_writable($path)) {
            throw new ConfigurationException(
                "Path {$path} is not writable. Please, set valid permissions for folder to store scenarios."
            );
        }

        $path .= DIRECTORY_SEPARATOR . $suite;
        if (!$input->getOption('single-file')) {
            @mkdir($path);
        }

        $suiteManager = new SuiteManager(new EventDispatcher(), $suite, $suiteConf, []);

        if ($suiteConf['bootstrap'] && file_exists($suiteConf['path'] . $suiteConf['bootstrap'])) {
            require_once $suiteConf['path'] . $suiteConf['bootstrap'];
        }

        $tests = $this->getTests($suiteManager);
        $scenarios = '';

        $output->writeln('<comment>This command is deprecated and will be removed in the next major version of Codeception.</comment>');

        foreach ($tests as $test) {
            if (!$test instanceof ScenarioDriven || !$test instanceof Descriptive) {
                continue;
            }
            $feature = $test->getScenarioText($format);

            $name = $this->underscore(basename($test->getFileName(), '.php'));

            // create separate file for each test in Cest
            if ($test instanceof Cest && !$input->getOption('single-file')) {
                $name .= '.' . $this->underscore($test->getTestMethod());
            }

            if ($input->getOption('single-file')) {
                $scenarios .= $feature;
                $output->writeln("* {$name} rendered");
            } else {
                $feature = $this->decorate($feature, $format);
                $this->createFile($path . DIRECTORY_SEPARATOR . $name . $this->formatExtension($format), $feature, true);
                $output->writeln("* {$name} generated");
            }
        }

        if ($input->getOption('single-file')) {
            $this->createFile($path . $this->formatExtension($format), $this->decorate($scenarios, $format), true);
        }

        return Command::SUCCESS;
    }

    protected function decorate(string $text, string $format): string
    {
        if ($format === 'html') {
            return "<html><body>{$text}</body></html>";
        }
        return $text;
    }

    protected function getTests($suiteManager)
    {
        $suiteManager->loadTests();
        return $suiteManager->getSuite()->getTests();
    }

    protected function formatExtension(string $format): string
    {
        return '.' . ($format === 'html' ? 'html' : 'txt');
    }

    private function underscore(string $name): string
    {
        $name = preg_replace('#([A-Z]+)([A-Z][a-z])#', '\\1_\\2', $name);
        $name = preg_replace('#([a-z\d])([A-Z])#', '\\1_\\2', $name);
        $name = str_replace(['/', '\\'], ['.', '.'], $name);
        $name = preg_replace('#_Cept$#', '', $name);
        return preg_replace('#_Cest$#', '', $name);
    }
}
