<?php
namespace Codeception;
use \Symfony\Component\Finder\Finder;

class CodeCoverageSettings
{

    /**
     * @var \PHP_CodeCoverage
     */
    protected $phpCodeCoverage = null;

    /**
     * @var CodeCoverageSettings
     */
    protected static $c3;

    /**
     * @var \PHP_CodeCoverage_Filter
     */
    protected $filter = null;

    function __construct($phpCoverage)
    {
        $this->phpCodeCoverage = $phpCoverage
            ? $phpCoverage
            : new \PHP_CodeCoverage;

        $this->filter = $this->phpCodeCoverage->filter();
    }

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

    public function filterWhiteList($config)
    {
        $filter = $this->filter;
        if (!isset($config['coverage'])) return;
        $coverage = $config['coverage'];
        if (!isset($coverage['whitelist'])) {
            $coverage['whitelist'] = array();
            if (isset($coverage['include'])) $coverage['whitelist']['include'] = $coverage['include'];
            if (isset($coverage['exclude'])) $coverage['whitelist']['exclude'] = $coverage['exclude'];
        }

        if (isset($coverage['whitelist']['include'])) {
            foreach ($coverage['whitelist']['include'] as $fileOrDir) {
                $finder = strpos($fileOrDir, '*') === false
                    ? array($fileOrDir)
                    : $this->matchWildcardPattern($fileOrDir);

                foreach ($finder as $file) {
                    $filter->addFileToWhitelist($file);
                }
            }
        }
        if (isset($coverage['whitelist']['exclude'])) {
            foreach ($coverage['whitelist']['exclude'] as $fileOrDir) {
                $finder = strpos($fileOrDir, '*') === false
                    ? array($fileOrDir)
                    : $this->matchWildcardPattern($fileOrDir);

                foreach ($finder as $file) {
                    $filter->removeFileFromWhitelist($file);
                }
            }
        }
        return $this;
    }

    public function filterBlackList($config)
    {
        $filter = $this->filter;
        if (!isset($config['coverage'])) return;
        $coverage = $config['coverage'];
        if (isset($coverage['blacklist'])) {
            if (isset($coverage['blacklist']['include'])) {
                foreach ($coverage['blacklist']['include'] as $fileOrDir) {
                    $finder = strpos($fileOrDir, '*') === false
                        ? array($fileOrDir)
                        : $this->matchWildcardPattern($fileOrDir);

                    foreach ($finder as $file) {
                        $filter->addFileToBlacklist($file);
                    }
                }
            }
            if (isset($coverage['blacklist']['exclude'])) {
                foreach ($coverage['blacklist']['exclude'] as $fileOrDir) {
                    $finder = strpos($fileOrDir, '*') === false
                        ? array($fileOrDir)
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
                $finder->in(\Codeception\Configuration::projectDir() . implode('/', $parts));
            } else {
                $finder->in(\Codeception\Configuration::projectDir() . implode('/', $parts) . '/' . $last_path);
            }
        }
        $finder->ignoreVCS(true)->files();
        return $finder;
    }

}
