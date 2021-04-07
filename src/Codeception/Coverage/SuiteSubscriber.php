<?php

declare(strict_types=1);

namespace Codeception\Coverage;

use Codeception\Configuration;
use Codeception\Coverage\Subscriber\Printer;
use Codeception\Exception\ConfigurationException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Interfaces\Remote as RemoteInterface;
use Codeception\PHPUnit\Compatibility\PHPUnit9;
use Codeception\Stub;
use Codeception\Subscriber\Shared\StaticEventsTrait;
use Exception;
use PHPUnit\Framework\CodeCoverageException;
use PHPUnit\Framework\TestResult;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Driver as CodeCoverageDriver;
use SebastianBergmann\CodeCoverage\Filter as CodeCoverageFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function array_keys;

abstract class SuiteSubscriber implements EventSubscriberInterface
{
    use StaticEventsTrait;

    /**
     * @var array
     */
    protected $defaultSettings = [
        'enabled'        => false,
        'remote'         => false,
        'local'          => false,
        'xdebug_session' => 'codeception',
        'remote_config'  => null,
        'show_uncovered' => false,
        'c3_url'         => null,
        'work_dir'       => null,
        'cookie_domain'  => null,
        'path_coverage'  => false,
    ];
    /**
     * @var array
     */
    protected $settings = [];
    /**
     * @var array
     */
    protected $filters = [];
    /**
     * @var array
     */
    protected $modules = [];
    /**
     * @var CodeCoverage|null
     */
    protected $coverage;
    /**
     * @var string
     */
    protected $logDir;
    /**
     * @var array
     */
    protected $options = [];
    /**
     * @var array
     */
    public static $events = [];

    abstract protected function isEnabled();

    /**
     * SuiteSubscriber constructor.
     *
     * @throws ConfigurationException
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
        $this->logDir = Configuration::outputDir();
    }

    /**
     * @throws Exception
     */
    protected function applySettings(array $settings): void
    {
        try {
            $this->coverage = PhpCodeCoverageFactory::build();
        } catch (CodeCoverageException $e) {
            throw new Exception(
                'XDebug is required to collect CodeCoverage. Please install xdebug extension and enable it in php.ini',
                $e->getCode(),
                $e
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

        if ($this->settings['show_uncovered']) {
            $this->coverage->processUncoveredFiles();
        } else {
            $this->coverage->doNotProcessUncoveredFiles();
        }
    }

    protected function getServerConnectionModule(array $modules): ?RemoteInterface
    {
        foreach ($modules as $module) {
            if ($module instanceof RemoteInterface) {
                return $module;
            }
        }
        return null;
    }

    /**
     * @throws ConfigurationException|ModuleException|Exception
     */
    public function applyFilter(TestResult $result): void
    {
        $driver = Stub::makeEmpty(CodeCoverageDriver::class);

        if (PHPUnit9::setCodeCoverageMethodExists($result)) {
            $result->setCodeCoverage(new CodeCoverage($driver, new CodeCoverageFilter()));
        }

        Filter::setup($this->coverage)
            ->whiteList($this->filters)
            ->blackList($this->filters);

        if (PHPUnit9::setCodeCoverageMethodExists($result)) {
            $result->setCodeCoverage($this->coverage);
        }
    }

    protected function mergeToPrint(CodeCoverage $coverage): void
    {
        Printer::$coverage->merge($coverage);
    }
}
