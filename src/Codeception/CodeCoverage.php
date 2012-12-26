<?php
namespace Codeception;
use \Symfony\Component\Finder\Finder;

class CodeCoverage
{

    protected $enabled = false;

    // defaults
    protected $settings = array('low_limit' => '35', 'high_limit' => '70', 'show_uncovered' => false);

    /**
     * @var \PHP_CodeCoverage
     */
    protected $phpCodeCoverage = null;

    protected $config;

    function __construct(\PHP_CodeCoverage $phpCoverage = null)
    {
        $this->config = \Codeception\Configuration::config();
        if (!isset($this->config['coverage'])) {
            $this->enabled = false;
        } elseif (isset($this->config['coverage']['enabled'])) {
            $this->enabled = (boolean)$this->config['coverage']['enabled'];
        } else {
            $this->enabled = true;
        }
        if (!$this->enabled) return;

        $this->phpCodeCoverage = $phpCoverage
            ? $phpCoverage
            : new \PHP_CodeCoverage;

        $filter = $this->phpCodeCoverage->filter();

        $this->filterWhiteList($filter);
        $this->filterBlackList($filter);
        $this->applySettings();
    }

    /**
     * @return null|\PHP_CodeCoverage
     */
    public function getPhpCodeCoverage()
    {
        return $this->phpCodeCoverage;
    }

    public function attachToResult(\PHPUnit_Framework_TestResult $result)
    {
        if (!$this->enabled) return;
        $result->setCodeCoverage($this->phpCodeCoverage);
    }

    public function printText($printer)
    {
        if (!$this->enabled) return;
        $writer = new \PHP_CodeCoverage_Report_Text(
            $printer, $this->settings['low_limit'], $this->settings['high_limit'], $this->settings['show_uncovered']
        );
        $writer->process($this->phpCodeCoverage, $this->config['settings']['colors']);
    }

    function printHtml()
    {
        if (!$this->enabled) return;
        $writer = new \PHP_CodeCoverage_Report_HTML(
          'UTF-8',
          true,
          $this->settings['low_limit'],
          $this->settings['high_limit'],
          sprintf(', <a href="http://codeception.com">Codeception</a> and <a href="http://phpunit.de/">PHPUnit %s</a>', \PHPUnit_Runner_Version::id()
          )
        );

        @mkdir(Configuration::logDir().'coverage');
        $writer->process($this->phpCodeCoverage, Configuration::logDir().'coverage');
    }

    public function printXml()
    {
        if (!$this->enabled) return;
        $writer = new \PHP_CodeCoverage_Report_Clover;
        $writer->process($this->phpCodeCoverage, Configuration::logDir().'coverage.xml');
    }

    protected function filterWhiteList(\PHP_CodeCoverage_Filter $filter)
    {
        $config = $this->config;
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
    }

    protected function filterBlackList(\PHP_CodeCoverage_Filter $filter)
    {
        $config = $this->config;
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

    protected function applySettings()
    {
        $keys = array_keys($this->settings);
        foreach ($keys as $key) {
            if (isset($this->config['coverage'][$key])) {
                $this->settings[$key] = $this->config['coverage'][$key];
            }
        }
    }


}
