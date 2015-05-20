<?php
namespace Codeception\Coverage;

use Codeception\Configuration;
use Codeception\Coverage\Subscriber\Printer;
use Codeception\Coverage\DummyCodeCoverage;
use Codeception\Subscriber\Shared\StaticEvents;
use Codeception\Lib\Interfaces\Remote;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class SuiteSubscriber implements EventSubscriberInterface {

    use StaticEvents;

    protected $defaultSettings = [
        'enabled' => false,
        'remote' => false,
        'local' => false,
        'xdebug_session' => 'codeception',
        'remote_config'  => null,
        'show_uncovered' => false,
        'c3_url' => null
    ];

    protected $settings = [];
    protected $filters = [];

    protected $coverage;
    protected $logDir;
    protected $options;
    static $events = [];

    abstract protected function isEnabled();

    function __construct($options = [])
    {
        $this->options = $options;
        $this->logDir = Configuration::outputDir();
    }

    protected function applySettings($settings)
    {
        if (!function_exists('xdebug_is_enabled')) {
            throw new \Exception('XDebug is required to collect CodeCoverage. Please install xdebug extension and enable it in php.ini');
        }
        $this->coverage = new \PHP_CodeCoverage();

        $this->filters = $settings;
        $this->settings = $this->defaultSettings;
        $keys = array_keys($this->defaultSettings);
        foreach ($keys as $key) {
            if (isset($settings['coverage'][$key])) {
                $this->settings[$key] = $settings['coverage'][$key];
            }
        }
        $this->coverage->setProcessUncoveredFilesFromWhitelist($this->settings['show_uncovered']);
    }

    /**
     * @return \Codeception\Lib\Interfaces\Remote|null
     */
    protected function getServerConnectionModule()
    {
        foreach (\Codeception\SuiteManager::$modules as $module) {
            if ($module instanceof Remote) {
                return $module;
            }
        }
        return null;
    }

    public function applyFilter(\PHPUnit_Framework_TestResult $result)
    {
        $result->setCodeCoverage(new DummyCodeCoverage());

        Filter::setup($this->coverage)
            ->whiteList($this->filters)
            ->blackList($this->filters);

        $result->setCodeCoverage($this->coverage);
    }

    protected function mergeToPrint($coverage)
    {
        Printer::$coverage->merge($coverage);
    }

}