<?php
namespace Codeception\Coverage;

use Codeception\Configuration;
use Codeception\Coverage\Subscriber\Printer;
use Codeception\Lib\Interfaces\Remote;
use Codeception\Stub;
use Codeception\Subscriber\Shared\StaticEvents;
use PHPUnit\Framework\CodeCoverageException;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class SuiteSubscriber implements EventSubscriberInterface
{
    use StaticEvents;

    protected $defaultSettings = [
        'enabled'        => false,
        'remote'         => false,
        'local'          => false,
        'xdebug_session' => 'codeception',
        'remote_config'  => null,
        'show_uncovered' => false,
        'c3_url'         => null,
        'work_dir'       => null
    ];

    protected $settings = [];
    protected $filters = [];
    protected $modules = [];

    protected $coverage;
    protected $logDir;
    protected $options;
    public static $events = [];

    abstract protected function isEnabled();

    public function __construct($options = [])
    {
        $this->options = $options;
        $this->logDir = Configuration::outputDir();
    }

    protected function applySettings($settings)
    {
        try {
            $this->coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage();
        } catch (CodeCoverageException $e) {
            throw new \Exception(
                'XDebug is required to collect CodeCoverage. Please install xdebug extension and enable it in php.ini'
            );
        }

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
     * @param array $modules
     * @return \Codeception\Lib\Interfaces\Remote|null
     */
    protected function getServerConnectionModule(array $modules)
    {
        foreach ($modules as $module) {
            if ($module instanceof Remote) {
                return $module;
            }
        }
        return null;
    }

    public function applyFilter(\PHPUnit\Framework\TestResult $result)
    {
        $driver = Stub::makeEmpty('SebastianBergmann\CodeCoverage\Driver\Driver');
        $result->setCodeCoverage(new CodeCoverage($driver));

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
