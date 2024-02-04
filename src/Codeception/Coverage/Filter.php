<?php

declare(strict_types=1);

namespace Codeception\Coverage;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Notification;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

use function array_pop;
use function explode;
use function implode;
use function is_array;
use function method_exists;
use function str_replace;

class Filter
{
    protected static ?Filter $c3 = null;

    protected ?\SebastianBergmann\CodeCoverage\Filter $filter = null;

    public function __construct(protected ?CodeCoverage $phpCodeCoverage)
    {
        $this->filter = $this->phpCodeCoverage->filter();
    }

    public static function setup(CodeCoverage $phpCoverage): self
    {
        self::$c3 = new self($phpCoverage);
        return self::$c3;
    }

    /**
     * @throws ConfigurationException
     */
    public function whiteList(array $config): self
    {
        $filter = $this->filter;
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

        if (isset($coverage['whitelist']['include'])) {
            if (!is_array($coverage['whitelist']['include'])) {
                throw new ConfigurationException('Error parsing yaml. Config `whitelist: include:` should be an array');
            }
            foreach ($coverage['whitelist']['include'] as $fileOrDir) {
                $finder = !str_contains($fileOrDir, '*')
                    ? [Configuration::projectDir() . DIRECTORY_SEPARATOR . $fileOrDir]
                    : $this->matchWildcardPattern($fileOrDir);

                foreach ($finder as $file) {
                    $filter->includeFile((string)$file);
                }
            }
        }

        if (isset($coverage['whitelist']['exclude'])) {
            if (!is_array($coverage['whitelist']['exclude'])) {
                throw new ConfigurationException('Error parsing yaml. Config `whitelist: exclude:` should be an array');
            }
            if (method_exists($filter, 'excludeFile')) {
                foreach ($coverage['whitelist']['exclude'] as $fileOrDir) {
                    try {
                        $finder = !str_contains($fileOrDir, '*')
                            ? [Configuration::projectDir() . DIRECTORY_SEPARATOR . $fileOrDir]
                            : $this->matchWildcardPattern($fileOrDir);

                        foreach ($finder as $file) {
                            $filter->excludeFile((string)$file);
                        }
                    } catch (DirectoryNotFoundException) {
                        continue;
                    }
                }
            } else {
                Notification::warning('`coverage: whitelist: exclude` option has no effect since PHPUnit 11.0', '');
            }
        }
        return $this;
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
            if ($lastPath === '*') {
                $finder->in(Configuration::projectDir() . implode('/', $parts));
            } else {
                $finder->in(Configuration::projectDir() . implode('/', $parts) . '/' . $lastPath);
            }
        }
        $finder->ignoreVCS(true)->files();
        return $finder;
    }

    public function getFilter(): \SebastianBergmann\CodeCoverage\Filter
    {
        return $this->filter;
    }
}
