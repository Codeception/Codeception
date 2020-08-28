<?php
namespace Codeception\Coverage;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use Codeception\Exception\ModuleException;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

class Filter
{
    /**
     * @var CodeCoverage
     */
    protected $phpCodeCoverage = null;

    /**
     * @var Filter
     */
    protected static $c3;

    /**
     * @var \SebastianBergmann\CodeCoverage\Filter
     */
    protected $filter = null;

    public function __construct(CodeCoverage $phpCoverage)
    {
        $this->phpCodeCoverage = $phpCoverage;
        $this->filter = $this->phpCodeCoverage->filter();
    }

    /**
     * @param CodeCoverage $phpCoverage
     * @return Filter
     */
    public static function setup(CodeCoverage $phpCoverage)
    {
        self::$c3 = new self($phpCoverage);
        return self::$c3;
    }

    /**
     * @return null|CodeCoverage
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
            if (!is_array($coverage['whitelist']['include'])) {
                throw new ConfigurationException('Error parsing yaml. Config `whitelist: include:` should be an array');
            }
            foreach ($coverage['whitelist']['include'] as $fileOrDir) {
                $finder = strpos($fileOrDir, '*') === false
                    ? [Configuration::projectDir() . DIRECTORY_SEPARATOR . $fileOrDir]
                    : $this->matchWildcardPattern($fileOrDir);

                foreach ($finder as $file) {
                    if (method_exists($filter, 'addFileToWhitelist')) {
                        //php-code-coverage 8 or older
                        $filter->addFileToWhitelist($file);
                    } else {
                        //php-code-coverage 9+
                        $filter->includeFile($file);
                    }
                }
            }
        }

        if (isset($coverage['whitelist']['exclude'])) {
            if (!is_array($coverage['whitelist']['exclude'])) {
                throw new ConfigurationException('Error parsing yaml. Config `whitelist: exclude:` should be an array');
            }
            foreach ($coverage['whitelist']['exclude'] as $fileOrDir) {
                try {
                    $finder = strpos($fileOrDir, '*') === false
                        ? [Configuration::projectDir() . DIRECTORY_SEPARATOR . $fileOrDir]
                        : $this->matchWildcardPattern($fileOrDir);

                    foreach ($finder as $file) {
                        if (method_exists($filter, 'removeFileFromWhitelist')) {
                            //php-code-coverage 8 or older
                            $filter->removeFileFromWhitelist($file);
                        } else {
                            //php-code-coverage 9+
                            $filter->excludeFile($file);
                        }
                    }
                } catch (DirectoryNotFoundException $e) {
                    continue;
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
                        ? [Configuration::projectDir() . DIRECTORY_SEPARATOR . $fileOrDir]
                        : $this->matchWildcardPattern($fileOrDir);

                    foreach ($finder as $file) {
                        $filter->addFileToBlacklist($file);
                    }
                }
            }
            if (isset($coverage['blacklist']['exclude'])) {
                foreach ($coverage['blacklist']['exclude'] as $fileOrDir) {
                    $finder = strpos($fileOrDir, '*') === false
                        ? [Configuration::projectDir() . DIRECTORY_SEPARATOR . $fileOrDir]
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
     * @return \SebastianBergmann\CodeCoverage\Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }
}
