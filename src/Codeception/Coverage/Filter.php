<?php

declare(strict_types=1);

namespace Codeception\Coverage;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use Codeception\Exception\ModuleException;
use PHPUnit\Runner\Version as PHPUnitVersion;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter as PhpUnitFilter;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

use function array_pop;
use function explode;
use function implode;
use function is_array;
use function iterator_to_array;
use function str_replace;

class Filter
{
    protected static ?self $codeceptionFilter = null;

    protected ?PhpUnitFilter $phpUnitFilter = null;

    public function __construct(protected ?CodeCoverage $phpCodeCoverage)
    {
        $this->phpUnitFilter = $this->phpCodeCoverage->filter();
    }

    public static function setup(CodeCoverage $phpCoverage): self
    {
        self::$codeceptionFilter = new self($phpCoverage);
        return self::$codeceptionFilter;
    }

    /**
     * @throws ConfigurationException
     */
    public function whiteList(array $config): self
    {
        $filter = $this->phpUnitFilter;
        if (!isset($config['coverage'])) {
            return $this;
        }
        $coverage = $config['coverage'];
        if (!isset($coverage['whitelist'])) {
            $coverage['whitelist'] = [];
            if (isset($coverage['include'])) {
                $coverage['whitelist']['include'] = $coverage['include'];
            }
            if (isset($coverage['exclude'])) {
                $coverage['whitelist']['exclude'] = $coverage['exclude'];
            }
        }

        if (PHPUnitVersion::series() >= 11) {
            return $this->newWhiteList($coverage['whitelist']);
        }

        foreach (['include', 'exclude'] as $type) {
            if (!isset($coverage['whitelist'][$type])) {
                continue;
            }

            if (!is_array($coverage['whitelist'][$type])) {
                throw new ConfigurationException("Error parsing yaml. Config `whitelist: {$type}:` should be an array");
            }

            foreach ($coverage['whitelist'][$type] as $fileOrDir) {
                try {
                    $finder = str_contains($fileOrDir, '*')
                        ? $this->matchWildcardPattern($fileOrDir)
                        : [Configuration::projectDir() . DIRECTORY_SEPARATOR . $fileOrDir];

                    foreach ($finder as $file) {
                        $file = (string) $file;
                        $type === 'include' ? $filter->includeFile($file) : $filter->excludeFile($file);
                    }
                } catch (DirectoryNotFoundException) {
                    continue;
                }
            }
        }

        return $this;
    }

    private function newWhiteList(array $whitelist): self
    {
        $include = $whitelist['include'] ?? [];
        $exclude = $whitelist['exclude'] ?? [];

        if (!is_array($include)) {
            throw new ConfigurationException('Error parsing yaml. Config `whitelist: include:` should be an array');
        }
        if (!is_array($exclude)) {
            throw new ConfigurationException('Error parsing yaml. Config `whitelist: exclude:` should be an array');
        }

        if ($exclude === [] && $include === []) {
            return $this;
        }

        if ($include === []) {
            $include = [
                Configuration::projectDir() . DIRECTORY_SEPARATOR . '*'
            ];
        }

        $allIncludedFiles = $this->matchFiles($include);
        $allExcludedFiles = $this->matchFiles($exclude);

        $coveredFiles = array_diff($allIncludedFiles, $allExcludedFiles);

        foreach ($coveredFiles as $coveredFile) {
            $this->phpUnitFilter->includeFile((string) $coveredFile);
        }

        return $this;
    }

    private function matchFiles(array $files): array
    {
        $matchedFiles = [];

        foreach ($files as $fileOrDir) {
            try {
                $finder = str_contains($fileOrDir, '*')
                    ? $this->matchWildcardPattern($fileOrDir)
                    : $this->matchFileOrDirectory($fileOrDir);

                $matchedFiles += iterator_to_array($finder->getIterator());
            } catch (DirectoryNotFoundException) {
                continue;
            }
        }

        return $matchedFiles;
    }

    /**
     * @throws ModuleException
     */
    public function blackList(array $config): self
    {
        if (isset($config['coverage']['blacklist'])) {
            throw new ModuleException($this, 'The blacklist functionality has been removed from PHPUnit 5,'
                . ' please remove blacklist section from configuration.');
        }
        return $this;
    }

    private function matchFileOrDirectory(string $fileOrDir): Finder
    {
        $fullPath = Configuration::projectDir() . $fileOrDir;
        $finder = Finder::create();
        if (is_dir($fullPath)) {
            $finder->in($fullPath);
            $finder->name('*.php');
        } else {
            $finder->in(dirname($fullPath));
            $finder->name(basename($fullPath));
        }
        $finder->ignoreVCS(true)->files();
        return $finder;
    }

    protected function matchWildcardPattern(string $pattern): Finder
    {
        $finder = Finder::create();
        $fileOrDir = str_replace('\\', '/', $pattern);
        $parts = explode('/', $fileOrDir);
        $file = array_pop($parts);
        if ($file === '*') {
            $file = '*.php';
        }
        $finder->name($file);
        if ($parts !== []) {
            $lastPath = array_pop($parts);
            $path = implode('/', ($lastPath === '*' ? $parts : [...$parts, $lastPath]));
            $finder->in(Configuration::projectDir() . $path);
        }
        $finder->ignoreVCS(true)->files();
        return $finder;
    }

    public function getFilter(): PhpUnitFilter
    {
        return $this->phpUnitFilter;
    }
}
