<?php

declare(strict_types=1);

namespace Codeception\Coverage\Subscriber;

use Codeception\Configuration;
use Codeception\Coverage\SuiteSubscriber;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Exception\ModuleException;
use Codeception\Exception\RemoteException;
use Codeception\Lib\Interfaces\Web as WebInterface;
use Codeception\Lib\Notification;
use Codeception\Module\WebDriver as WebDriverModule;
use Facebook\WebDriver\Exception\NoSuchAlertException;
use RuntimeException;
use SebastianBergmann\CodeCoverage\CodeCoverage;

use function array_filter;
use function array_replace_recursive;
use function file_exists;
use function file_get_contents;
use function json_encode;
use function parse_url;
use function preg_match;
use function str_replace;
use function stream_context_create;
use function unserialize;
use function usleep;

/**
 * When collecting code coverage data from local server HTTP requests are sent to c3.php file.
 * Coverage Collection is started by sending cookies/headers.
 * Result is taken from the local file and merged with local code coverage results.
 *
 * Class LocalServer
 * @package Codeception\Coverage\Subscriber
 */
class LocalServer extends SuiteSubscriber
{
    // headers

    /**
     * @var string
     */
    public const COVERAGE_HEADER = 'X-Codeception-CodeCoverage';
    /**
     * @var string
     */
    public const COVERAGE_HEADER_ERROR = 'X-Codeception-CodeCoverage-Error';
    /**
     * @var string
     */
    public const COVERAGE_HEADER_CONFIG = 'X-Codeception-CodeCoverage-Config';
    /**
     * @var string
     */
    public const COVERAGE_HEADER_SUITE = 'X-Codeception-CodeCoverage-Suite';

    // cookie names

    /**
     * @var string
     */
    public const COVERAGE_COOKIE = 'CODECEPTION_CODECOVERAGE';
    /**
     * @var string
     */
    public const COVERAGE_COOKIE_ERROR = 'CODECEPTION_CODECOVERAGE_ERROR';

    protected string $suiteName = '';

    protected array $c3Access = [
        'http' => [
            'method' => 'GET',
            'header' => ''
        ]
    ];

    protected ?WebInterface $module = null;

    /**
     * @var array<string, string>
     */
    public static array $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::TEST_BEFORE  => 'beforeTest',
        Events::STEP_AFTER   => 'afterStep',
        Events::SUITE_AFTER  => 'afterSuite',
    ];

    protected function isEnabled(): bool
    {
        return $this->module && !$this->settings['remote'] && $this->settings['enabled'];
    }

    public function beforeSuite(SuiteEvent $event): void
    {
        $this->module = $this->getServerConnectionModule($event->getSuite()->getModules());
        $this->applySettings($event->getSettings());
        if (!$this->isEnabled()) {
            return;
        }

        $this->suiteName = $event->getSuite()->getBaseName();

        if ($this->settings['remote_config']) {
            $this->addC3AccessHeader(self::COVERAGE_HEADER_CONFIG, $this->settings['remote_config']);
            if ($this->c3Request('clear') === false) {
                throw new RemoteException(
                    '
                    CodeCoverage Error.
                    Check the file "c3.php" is included in your application.
                    We tried to access "/c3/report/clear" but this URI was not accessible.
                    You can review actual error messages in c3tmp dir.
                    '
                );
            }
        }
    }

    public function beforeTest(TestEvent $event): void
    {
        if (!$this->isEnabled()) {
            return;
        }
        $this->startCoverageCollection($event->getTest()->getName());
    }

    public function afterStep(StepEvent $event): void
    {
        if (!$this->isEnabled()) {
            return;
        }
        $this->fetchErrors();
    }

    public function afterSuite(SuiteEvent $event): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $outputDir = Configuration::outputDir() . 'c3tmp/';
        $blockFile = $outputDir . 'block_report';
        $coverageFile = $outputDir . 'codecoverage.serialized';
        $errorFile = $outputDir . 'error.txt';

        $this->waitForFile($blockFile, 120, 250_000);
        $this->waitForFile($coverageFile, 5, 500_000);

        if (!file_exists($coverageFile)) {
            throw new RuntimeException(
                file_exists($errorFile) ? file_get_contents($errorFile) : "Code coverage file {$coverageFile} does not exist"
            );
        }

        if ($coverage = @unserialize(file_get_contents($coverageFile))) {
            $this->preProcessCoverage($coverage)->mergeToPrint($coverage);
        }
    }

    /**
     * Allows Translating Remote Paths To Local (IE: When Using Docker)
     */
    protected function preProcessCoverage(CodeCoverage $coverage): self
    {
        if (!$this->settings['work_dir']) {
            return $this;
        }

        $workDir = rtrim((string) $this->settings['work_dir'], '/\\') . DIRECTORY_SEPARATOR;
        $projectDir = Configuration::projectDir();
        $coverageData = $coverage->getData(true); // We only want covered files, not all whitelisted ones.

        codecept_debug("Replacing all instances of {$workDir} with {$projectDir}");

        foreach ($coverageData as $path => $datum) {
            unset($coverageData[$path]);
            $path = str_replace($workDir, $projectDir, (string) $path);
            $coverageData[$path] = $datum;
        }
        $coverage->setData($coverageData);

        return $this;
    }

    protected function c3Request(string $action): string|false
    {
        $this->addC3AccessHeader(self::COVERAGE_HEADER, 'remote-access');
        $context = stream_context_create($this->c3Access);
        $c3Url = $this->settings['c3_url'] ?? $this->module->_getUrl();
        $contents = file_get_contents("{$c3Url}/c3/report/{$action}", false, $context);

        $okHeaders = array_filter(
            $http_response_header,
            fn ($h) => preg_match('#^HTTP(.*?)\s200#', $h)
        );
        if ($okHeaders === []) {
            throw new RemoteException("Request was not successful. See response header: " . $http_response_header[0]);
        }
        if ($contents === false) {
            $this->getRemoteError($http_response_header);
        }
        return $contents;
    }

    protected function startCoverageCollection(string $testName): void
    {
        $coverageDataJson = json_encode([
            'CodeCoverage'        => $testName,
            'CodeCoverage_Suite'  => $this->suiteName,
            'CodeCoverage_Config' => $this->settings['remote_config']
        ], JSON_THROW_ON_ERROR);

        if ($this->module instanceof WebDriverModule) {
            $this->module->amOnPage('/');
        }

        $cookieDomain = $this->settings['cookie_domain'] ??
            parse_url($this->settings['c3_url'] ?? $this->module->_getUrl(), PHP_URL_HOST) ??
            'localhost';

        if (!$cookieDomain) {
            // we need to separate coverage cookies by host; we can't separate cookies by port.
            $cookieDomain = 'localhost';
        }

        $cookieParams = $cookieDomain !== 'localhost' ? ['domain' => $cookieDomain] : [];

        $this->module->setCookie(self::COVERAGE_COOKIE, $coverageDataJson, $cookieParams);
        // putting in configuration ensures the cookie is used for all sessions of a MultiSession test

        $cookies = $this->module->_getConfig('cookies');
        if (!is_array($cookies)) {
            $cookies = [];
        }

        $cookieUpdated = false;
        foreach ($cookies as &$cookie) {
            if (isset($cookie['Name'], $cookie['Value']) && $cookie['Name'] === self::COVERAGE_COOKIE) {
                $cookie['Value'] = $coverageDataJson;
                $cookieUpdated = true;
                break;
            }
            // \Codeception\Lib\InnerBrowser will complain about this
        }
        unset($cookie);

        if (!$cookieUpdated) {
            $cookies[] = [
                'Name' => self::COVERAGE_COOKIE,
                'Value' => $coverageDataJson
            ];
        }

        $this->module->_setConfig(['cookies' => $cookies]);
    }

    protected function fetchErrors(): void
    {
        // Calling grabCookie() while an alert is present dismisses the alert
        // @see https://github.com/Codeception/Codeception/issues/1485
        if ($this->module instanceof WebDriverModule) {
            try {
                $this->module->webDriver->switchTo()->alert()->getText();
                // If this succeeds an alert is present, abort
                return;
            } catch (NoSuchAlertException) {
                // No alert present, continue
            }
        }

        try {
            $error = $this->module->grabCookie(self::COVERAGE_COOKIE_ERROR);
        } catch (ModuleException) {
            // when a new session is started we can't get cookies because there is no
            // current page, but there can be no code coverage error either
            return;
        }
        if (!empty($error)) {
            $this->module->resetCookie(self::COVERAGE_COOKIE_ERROR);
            throw new RemoteException($error);
        }
    }

    /** @param string[] $headers */
    protected function getRemoteError(array $headers): void
    {
        foreach ($headers as $header) {
            if (str_starts_with($header, self::COVERAGE_HEADER_ERROR)) {
                throw new RemoteException($header);
            }
        }
    }

    protected function addC3AccessHeader(string $header, string $value): void
    {
        $headerString = "{$header}: {$value}\r\n";
        if (!str_contains((string) $this->c3Access['http']['header'], $headerString)) {
            $this->c3Access['http']['header'] .= $headerString;
        }
    }

    protected function applySettings(array $settings): void
    {
        parent::applySettings($settings);
        if (isset($settings['coverage']['remote_context_options'])) {
            $this->c3Access = array_replace_recursive($this->c3Access, $settings['coverage']['remote_context_options']);
        }
    }

    private function waitForFile(string $file, int $maxRetries, int $sleepTime): void
    {
        $retries = $maxRetries;
        while ($retries > 0 && (!file_exists($file) || file_get_contents($file) !== '0')) {
            usleep($sleepTime);
            $retries--;
        }

        if (!file_exists($file) || file_get_contents($file) !== '0') {
            Notification::warning('Timeout: Some coverage data is not included in the coverage report.', '');
        }
    }
}
