<?php
namespace Codeception\Coverage;

use Codeception\Configuration;
use Codeception\Exception\ModuleException;
use Symfony\Component\Finder\Finder;

class Filter
{
    /**
     * @var \PHP_CodeCoverage
     */
    protected $phpCodeCoverage = null;

    /**
     * @var Filter
     */
    protected static $c3;

    /**
     * @var \PHP_CodeCoverage_Filter
     */
    protected $filter = null;

    public function __construct(\PHP_CodeCoverage $phpCoverage)
    {
        $this->phpCodeCoverage = $phpCoverage
            ? $phpCoverage
            : new \PHP_CodeCoverage;

        $this->filter = $this->phpCodeCoverage->filter();
    }

    /**
     * @param \PHP_CodeCoverage $phpCoverage
     * @return Filter
     */
    public static function setup(\PHP_CodeCoverage $phpCoverage)
    {
        self::$c3 = new self($phpCoverage);
        return self::$c3;
    }

    /**
     * @return null|\PHP_CodeCoverage
     */
    public function getPhpCodeCoverage()
    {
        return $this->phpCodeCoverage;
    }

    /**
     * @param $config
     * @return Filter
     */
    public function whiteList($config)
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
            foreach ($coverage['whitelist']['include'] as $fileOrDir) {
                $finder = strpos($fileOrDir, '*') === false
                    ? [$fileOrDir]
                    : $this->matchWildcardPattern($fileOrDir);

                foreach ($finder as $file) {
                    $filter->addFileToWhitelist($file);
                }
            }
        }

        if (isset($coverage['whitelist']['exclude'])) {
            foreach ($coverage['whitelist']['exclude'] as $fileOrDir) {
                $finder = strpos($fileOrDir, '*') === false
                    ? [$fileOrDir]
                    : $this->matchWildcardPattern($fileOrDir);

                foreach ($finder as $file) {
                    $filter->removeFileFromWhitelist($file);
                }
            }
        }
        return $this;
    }

    /**
     * @param $config
     * @return Filter
     */
    public function blackList($config)
    {
        $filter = $this->filter;
        if (!isset($config['coverage'])) {
            return $this;
        }
        $coverage = $config['coverage'];
        if (isset($coverage['blacklist'])) {
            if (!method_exists($filter, 'addFileToBlacklist')) {
                throw new ModuleException($this, 'The blacklist functionality has been removed from PHPUnit 5,'
                . ' please remove blacklist section from configuration.');
            }

            if (isset($coverage['blacklist']['include'])) {
                foreach ($coverage['blacklist']['include'] as $fileOrDir) {
                    $finder = strpos($fileOrDir, '*') === false
                        ? [$fileOrDir]
                        : $this->matchWildcardPattern($fileOrDir);

                    foreach ($finder as $file) {
                        $filter->addFileToBlacklist($file);
                    }
                }
            }
            if (isset($coverage['blacklist']['exclude'])) {
                foreach ($coverage['blacklist']['exclude'] as $fileOrDir) {
                    $finder = strpos($fileOrDir, '*') === false
                        ? [$fileOrDir]
                        : $this->matchWildcardPattern($fileOrDir);

                    foreach ($finder as $file) {
                        $filter->removeFileFromBlacklist($file);
                    }
                }
            }
        }
        return $this;
    }

    protected function matchWildcardPattern($pattern)
    {
        $finder = Finder::create();
        $fileOrDir = str_replace('\\', '/', $pattern);
        $parts = explode('/', $fileOrDir);
        $file = array_pop($parts);
        $finder->name($file);
        if (count($parts)) {
            $last_path = array_pop($parts);
            if ($last_path === '*') {
                $finder->in(Configuration::projectDir() . implode('/', $parts));
            } else {
                $finder->in(Configuration::projectDir() . implode('/', $parts) . '/' . $last_path);
            }
        }
        $finder->ignoreVCS(true)->files();
        return $finder;
    }

    /**
     * @return \PHP_CodeCoverage_Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }
}
