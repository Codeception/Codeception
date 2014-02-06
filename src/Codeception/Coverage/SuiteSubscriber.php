<?php
namespace Codeception\Coverage;

use Codeception\Configuration;
use Codeception\Coverage\Subscriber\Printer;
use Codeception\Coverage\DummyCodeCoverage;
use Codeception\Subscriber\Shared\StaticEvents;
use Codeception\Lib\RemoteInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class SuiteSubscriber implements EventSubscriberInterface {

    use StaticEvents;

    protected $defaultSettings = [
        'enabled' => true,
        'remote' => false,
        'xdebug_session' => 'codeception',
        'remote_config'  => null
    ];
    protected $settings = [];

    protected $coverage;
    protected $logDir;
    protected $options;
    static $events = [];

    abstract protected function isEnabled();

    function __construct($options = [])
    {
        $this->options = $options;
        $this->logDir = Configuration::logDir();
    }

    protected function applySettings($settings)
    {
        if (!function_exists('xdebug_is_enabled')) {
            throw new \Exception('XDebug is required to collect CodeCoverage. Please install xdebug extension and enable it in php.ini');
        }
        $this->coverage = new \PHP_CodeCoverage();

        $this->settings = $this->defaultSettings;
        $keys = array_keys($this->defaultSettings);
        foreach ($keys as $key) {
            if (isset($settings['coverage'][$key])) {
                $this->settings[$key] = $settings['coverage'][$key];
            }
        }
    }

    /**
     * @return RemoteInterface|null
     */
    protected function getServerConnectionModule()
    {
        foreach (\Codeception\SuiteManager::$modules as $module) {
            if ($module instanceof RemoteInterface) {
                return $module;
            }
        }
        return null;
    }

    public function applyFilter(\PHPUnit_Framework_TestResult $result)
    {
        $result->setCodeCoverage(new DummyCodeCoverage());

        Filter::setup($this->coverage)
            ->whiteList($this->settings)
            ->blackList($this->settings);

        $result->setCodeCoverage($this->coverage);
    }

    protected function mergeToPrint($coverage)
    {
        Printer::$coverage->merge($coverage);
    }

}