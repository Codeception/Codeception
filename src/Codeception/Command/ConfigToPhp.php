<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Lib\Generator\PhpConfigFile;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

use function array_filter;
use function array_key_last;
use function array_merge;
use function basename;
use function dirname;
use function file_exists;
use function file_get_contents;
use function is_array;
use function glob;
use function is_dir;
use function preg_replace;
use function rtrim;

/**
 * Converts an existing YAML setup to the PHP config format (builder style).
 *
 *  * `codecept config:to-php` - converts codeception.yml and each *.suite.yml in the current project
 *  * `codecept config:to-php path/to/project` - converts a project in another directory
 *  * `codecept config:to-php --dry-run` - prints the generated PHP without writing files
 *
 * Existing `.php` files are never overwritten. Whole-value `%param%` placeholders become getenv()
 * (global) or Params::get() (suites); after converting, delete the old `.yml` files.
 */
#[AsCommand(
    name: 'config:to-php',
    description: 'Converts YAML configuration to the PHP format'
)]
class ConfigToPhp extends Command
{
    use Shared\FileSystemTrait;

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::OPTIONAL, 'Project directory or codeception.yml to convert', '.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Print generated PHP without writing files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = (string) $input->getArgument('path');
        $dir  = is_dir($path) ? rtrim($path, '/\\') : dirname($path);

        $dryRun   = (bool) $input->getOption('dry-run');
        $renderer = new PhpConfigFile();

        $candidates = is_dir($path)
            ? array_filter(
                [$dir . DIRECTORY_SEPARATOR . 'codeception.dist.yml', $dir . DIRECTORY_SEPARATOR . 'codeception.yml'],
                'file_exists'
            )
            : (file_exists($path) ? [$path] : []);
        if ($candidates === []) {
            $notFound = is_dir($path) ? $dir . DIRECTORY_SEPARATOR . 'codeception.yml' : $path;
            $output->writeln("<error>Global config not found: {$notFound}</error>");
            return Command::FAILURE;
        }

        $parsed = [];
        foreach ($candidates as $candidate) {
            $arr = $this->parseYaml($candidate);
            if ($arr === null) {
                $output->writeln("<error>Failed to read {$candidate}</error>");
                return Command::FAILURE;
            }
            $parsed[$candidate] = $arr;
        }

        $globalYml = array_key_last($parsed);
        $paths     = [];
        foreach ($parsed as $arr) {
            if (is_array($arr['paths'] ?? null)) {
                $paths = array_merge($paths, $arr['paths']);
            }
        }

        $this->emit($output, $globalYml, $renderer->renderGlobal($parsed[$globalYml]), $dryRun);

        $testsDir = $dir . DIRECTORY_SEPARATOR . ($paths['tests'] ?? 'tests');
        if (is_dir($testsDir)) {
            $suiteFiles = array_merge(
                glob($testsDir . DIRECTORY_SEPARATOR . '*.suite.yml') ?: [],
                glob($testsDir . DIRECTORY_SEPARATOR . '*.suite.dist.yml') ?: []
            );
            foreach ($suiteFiles as $suiteYml) {
                $suiteArr = $this->parseYaml($suiteYml);
                if ($suiteArr === null) {
                    $output->writeln("<comment>Skipped {$suiteYml} (could not be read)</comment>");
                    continue;
                }
                $this->emit($output, $suiteYml, $renderer->renderSuite($suiteArr), $dryRun);
            }
        }

        foreach ($renderer->warnings() as $warning) {
            $output->writeln("<comment>! {$warning}</comment>");
        }
        if (!$dryRun) {
            $output->writeln("\n<info>Done.</info> Review the generated PHP, then remove the old .yml files.");
        }
        return Command::SUCCESS;
    }

    private function parseYaml(string $file): ?array
    {
        $contents = file_get_contents($file);
        return $contents === false ? null : (Yaml::parse($contents) ?? []);
    }

    private function emit(OutputInterface $output, string $from, string $contents, bool $dryRun): void
    {
        $to = (string) preg_replace('/\.yml$/', '.php', $from);
        if ($dryRun) {
            $output->writeln("<info># {$to}</info>");
            $output->writeln($contents);
            return;
        }
        if (file_exists($to)) {
            $output->writeln("<comment>Skipped {$to} (already exists)</comment>");
            return;
        }
        $this->createFile($to, $contents);
        $output->writeln("<info>Created {$to}</info> from " . basename($from));
    }
}
