<?php

declare(strict_types=1);

namespace Codeception\Test;

use Codeception\Exception\ConfigurationException;
use Codeception\Test\Loader\Cept as CeptLoader;
use Codeception\Test\Loader\Cest as CestLoader;
use Codeception\Test\Loader\Gherkin as GherkinLoader;
use Codeception\Test\Loader\LoaderInterface;
use Codeception\Test\Loader\Unit as UnitLoader;
use Exception;
use Symfony\Component\Finder\Finder;

use function file_exists;
use function getcwd;
use function is_dir;
use function preg_match;
use function str_replace;

/**
 * Loads all Codeception supported test formats from a directory.
 *
 * ``` php
 * <?php
 * $testLoader = new \Codeception\TestLoader('tests/unit');
 * $testLoader->loadTests();
 * $tests = $testLoader->getTests();
 * ```
 * You can load specific file
 *
 * ``` php
 * <?php
 * $testLoader = new \Codeception\TestLoader('tests/unit');
 * $testLoader->loadTest('UserTest.php');
 * $testLoader->loadTest('PostTest.php');
 * $tests = $testLoader->getTests();
 * ```
 * or a subdirectory
 *
 * ``` php
 * <?php
 * $testLoader = new \Codeception\TestLoader('tests/unit');
 * $testLoader->loadTest('models'); // all tests from tests/unit/models
 * $tests = $testLoader->getTests();
 * ```
 *
 */
class Loader
{
    /** @var LoaderInterface[] */
    protected array $formats;
    protected array $tests = [];
    protected ?string $path;
    private readonly ?string $shard;

    public function __construct(array $suiteSettings)
    {
        $this->path = empty($suiteSettings['path']) ? null : rtrim((string) $suiteSettings['path'], "/\\") . '/';
        $this->shard = $suiteSettings['shard'] ?? null;

        $this->formats = [
            new CeptLoader(),
            new CestLoader($suiteSettings),
            new UnitLoader(),
            new GherkinLoader($suiteSettings),
        ];

        foreach ($suiteSettings['formats'] ?? [] as $format) {
            $this->formats[] = new $format($suiteSettings);
        }
    }

    public function getTests(): array
    {
        if ($this->shard === null) {
            return $this->tests;
        }

        if (sscanf(trim($this->shard), '%d/%d', $current, $total) !== 2) {
            throw new ConfigurationException('Shard must be set as --shard=CURRENT/TOTAL where both parts are numbers, e.g. --shard=1/3');
        }
        if ($current < 1) {
            throw new ConfigurationException("Incorrect shard index. Use 1/{$total} to start the first shard");
        }
        if ($total < $current) {
            throw new ConfigurationException('Total shards are less than current shard');
        }

        $chunks = $this->splitTestsIntoChunks($total);

        return $chunks[$current - 1] ?? [];
    }

    private function splitTestsIntoChunks(int $chunks): array
    {
        if ($this->tests === []) {
            return [];
        }

        return array_chunk($this->tests, (int) ceil(count($this->tests) / $chunks), true);
    }

    protected function relativeName(string $file): string
    {
        return str_replace('\\', '/', str_replace($this->path ?? '', '', $file));
    }

    protected function findPath(string $path): string
    {
        if (
            !file_exists($path)
            && !str_ends_with($path, '.php')
            && file_exists($newPath = $path . '.php')
        ) {
            return $newPath;
        }

        return $path;
    }

    protected function makePath(string $originalPath): string
    {
        $candidates = [
            $this->findPath(($this->path ?? '') . $this->relativeName($originalPath)),
            $this->findPath(getcwd() . "/{$originalPath}"),
        ];
        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        throw new Exception("File or path {$originalPath} not found");
    }

    public function loadTest(string $path): void
    {
        $path = $this->makePath($path);
        if (is_dir($path)) {
            $previous   = $this->path;
            $this->path = rtrim($path, "/\\") . '/';
            $this->loadTests();
            $this->path = $previous;
            return;
        }

        foreach ($this->formats as $format) {
            if (preg_match($format->getPattern(), $path)) {
                $format->loadTests($path);
                $this->tests = $format->getTests();
                return;
            }
        }

        throw new Exception('Test format not supported. Please, check you use the right suffix. Available filetypes: Cept, Cest, Test');
    }

    public function loadTests(?string $fileName = null): void
    {
        if ($fileName !== null) {
            $this->loadTest($fileName);
            return;
        }

        $files = Finder::create()->files()->sortByName()->in($this->path)->followLinks();
        foreach ($files as $file) {
            foreach ($this->formats as $format) {
                if (preg_match($format->getPattern(), $path = $file->getPathname())) {
                    $format->loadTests($path);
                    break;
                }
            }
        }

        foreach ($this->formats as $format) {
            $this->tests = [...$this->tests, ...$format->getTests()];
        }
    }
}
