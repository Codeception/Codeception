<?php

declare(strict_types=1);

namespace Codeception\Coverage;

use Codeception\Configuration;
use Codeception\Coverage\Subscriber\Printer;
use Codeception\Exception\ConfigurationException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Interfaces\Remote as RemoteInterface;
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
use function method_exists;

abstract class SuiteSubscriber implements EventSubscriberInterface
{
    use StaticEventsTrait;

    protected array $defaultSettings = [
        'enabled'        => false,
        'remote'         => false,
        'local'          => false,
        'xdebug_session' => 'codeception',
        'remote_config'  => null,
        'show_uncovered' => false,
        'c3_url'         => null,
        'work_dir'       => null,
        'cookie_domain'  => null,
    ];

    protected array $settings = [];

    protected array $filters = [];

    protected array $modules = [];

    protected ?CodeCoverage $coverage = null;

    protected string $logDir;

    protected array $options = [];

    public static array $events = [];

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
        if (method_exists($this->coverage, 'setProcessUncoveredFilesFromWhitelist')) {
            //php-code-coverage 8 or older
            $this->coverage->setProcessUncoveredFilesFromWhitelist($this->settings['show_uncovered']);
        } else {
            //php-code-coverage 9+
            if ($this->settings['show_uncovered']) {
                $this->coverage->processUncoveredFiles();
            } else {
                $this->coverage->doNotProcessUncoveredFiles();
            }
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
        $result->setCodeCoverage(new CodeCoverage($driver, new CodeCoverageFilter()));

        Filter::setup($this->coverage)
            ->whiteList($this->filters)
            ->blackList($this->filters);

        $result->setCodeCoverage($this->coverage);
    }

    protected function mergeToPrint(CodeCoverage $coverage): void
    {
        Printer::$coverage->merge($coverage);
    }
}
