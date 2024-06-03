<?php

declare(strict_types=1);

namespace Codeception\Coverage;

use Codeception\Configuration;
use Codeception\Coverage\Subscriber\Printer;
use Codeception\Exception\ConfigurationException;
use Codeception\Lib\Interfaces\Remote as RemoteInterface;
use Codeception\Subscriber\Shared\StaticEventsTrait;
use Exception;
use PHPUnit\Framework\CodeCoverageException;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function array_keys;

abstract class SuiteSubscriber implements EventSubscriberInterface
{
    use StaticEventsTrait;

    protected array $defaultSettings = [
        'enabled'                      => false,
        'remote'                       => false,
        'local'                        => false,
        'xdebug_session'               => 'codeception',
        'remote_config'                => null,
        'show_uncovered'               => false,
        'c3_url'                       => null,
        'work_dir'                     => null,
        'cookie_domain'                => null,
        'path_coverage'                => false,
        'strict_covers_annotation'     => false,
        'ignore_deprecated_code'       => false,
        'disable_code_coverage_ignore' => false,
    ];

    protected array $settings = [];

    protected array $filters = [];

    protected array $modules = [];

    protected ?CodeCoverage $coverage = null;

    protected string $logDir;

    public static array $events = [];

    abstract protected function isEnabled();

    /**
     * SuiteSubscriber constructor.
     *
     * @throws ConfigurationException
     */
    public function __construct(protected array $options = [])
    {
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

        $this->configureCoverage();
    }

    protected function configureCoverage(): void
    {
        if ($this->settings['strict_covers_annotation']) {
            $this->coverage->enableCheckForUnintentionallyCoveredCode();
        }

        if ($this->settings['ignore_deprecated_code']) {
            $this->coverage->ignoreDeprecatedCode();
        } else {
            $this->coverage->doNotIgnoreDeprecatedCode();
        }

        if ($this->settings['disable_code_coverage_ignore']) {
            $this->coverage->disableAnnotationsForIgnoringCode();
        } else {
            $this->coverage->enableAnnotationsForIgnoringCode();
        }

        if ($this->settings['show_uncovered']) {
            $this->coverage->includeUncoveredFiles();
        } else {
            $this->coverage->excludeUncoveredFiles();
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

    protected function mergeToPrint(CodeCoverage $coverage): void
    {
        Printer::$coverage->merge($coverage);
    }
}
