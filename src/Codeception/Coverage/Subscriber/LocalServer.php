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
use function array_key_exists;
use function array_replace_recursive;
use function codecept_debug;
use function file_exists;
use function file_get_contents;
use function is_array;
use function json_encode;
use function parse_url;
use function preg_match;
use function rtrim;
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
            'method' => "GET",
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
            $knock = $this->c3Request('clear');
            if ($knock === false) {
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

        // wait for all running tests to finish
        $blockfilename = Configuration::outputDir() . 'c3tmp/block_report';
        if (file_exists($blockfilename) && filesize($blockfilename) !== 0) {
            $retries = 120; // 30 sec total
            while (file_get_contents($blockfilename) !== '0' && --$retries >= 0) {
                usleep(250_000); // 0.25 sec
            }
            if (file_get_contents($blockfilename) !== '0' && $retries === -1) {
                Notification::warning(
                    'Timeout: Some coverage data is not included in the coverage report.',
                    '',
                );
            }
        }

        $coverageFile = Configuration::outputDir() . 'c3tmp/codecoverage.serialized';
        $retries = 5;
        while (!file_exists($coverageFile) && --$retries >= 0) {
            $seconds = (int)(0.5 * 1_000_000); // 0.5 sec
            usleep($seconds);
        }

        if (!file_exists($coverageFile)) {
            if (file_exists(Configuration::outputDir() . 'c3tmp/error.txt')) {
                throw new RuntimeException(file_get_contents(Configuration::outputDir() . 'c3tmp/error.txt'));
            }

            throw new RuntimeException('Code coverage file ' . $coverageFile . ' does not exist');
        }

        $contents = file_get_contents($coverageFile);
        $coverage = @unserialize($contents);
        if ($coverage === false) {
            return;
        }

        $this->preProcessCoverage($coverage)
            ->mergeToPrint($coverage);
    }

    /**
     * Allows Translating Remote Paths To Local (IE: When Using Docker)
     */
    protected function preProcessCoverage(CodeCoverage $coverage): self
    {
        //Only Process If Work Directory Set
        if ($this->settings['work_dir'] === null) {
            return $this;
        }

        $workDir = rtrim($this->settings['work_dir'], '/\\') . DIRECTORY_SEPARATOR;
        $projectDir = Configuration::projectDir();
        $coverageData = $coverage->getData(true); //We only want covered files, not all whitelisted ones.

        codecept_debug("Replacing all instances of {$workDir} with {$projectDir}");

        foreach ($coverageData as $path => $datum) {
            unset($coverageData[$path]);

            $path = str_replace($workDir, $projectDir, $path);

            $coverageData[$path] = $datum;
        }

        $coverage->setData($coverageData);

        return $this;
    }

    protected function c3Request(string $action): string|false
    {
        $this->addC3AccessHeader(self::COVERAGE_HEADER, 'remote-access');
        $context = stream_context_create($this->c3Access);
        $c3Url = $this->settings['c3_url'] ?: $this->module->_getUrl();
        $contents = file_get_contents($c3Url . '/c3/report/' . $action, false, $context);

        $okHeaders = array_filter(
            $http_response_header,
            fn ($h) => preg_match('#^HTTP(.*?)\s200#', $h)
        );
        if (empty($okHeaders)) {
            throw new RemoteException("Request was not successful. See response header: " . $http_response_header[0]);
        }
        if ($contents === false) {
            $this->getRemoteError($http_response_header);
        }
        return $contents;
    }

    protected function startCoverageCollection($testName): void
    {
        $value = [
            'CodeCoverage'        => $testName,
            'CodeCoverage_Suite'  => $this->suiteName,
            'CodeCoverage_Config' => $this->settings['remote_config']
        ];
        $value = json_encode($value, JSON_THROW_ON_ERROR);

        if ($this->module instanceof WebDriverModule) {
            $this->module->amOnPage('/');
        }

        $cookieDomain = $this->settings['cookie_domain'] ?? null;

        if (!$cookieDomain) {
            $c3Url = parse_url($this->settings['c3_url'] ?: $this->module->_getUrl());

            // we need to separate coverage cookies by host; we can't separate cookies by port.
            $cookieDomain = $c3Url['host'] ?? 'localhost';
        }

        $cookieParams = [];
        if ($cookieDomain !== 'localhost') {
            $cookieParams['domain'] = $cookieDomain;
        }

        $this->module->setCookie(self::COVERAGE_COOKIE, $value, $cookieParams);

        // putting in configuration ensures the cookie is used for all sessions of a MultiSession test

        $cookies = $this->module->_getConfig('cookies');
        if (!$cookies || !is_array($cookies)) {
            $cookies = [];
        }

        $found = false;
        foreach ($cookies as &$cookie) {
            if (!is_array($cookie) || !array_key_exists('Name', $cookie) || !array_key_exists('Value', $cookie)) {
                // \Codeception\Lib\InnerBrowser will complain about this
                continue;
            }
            if ($cookie['Name'] === self::COVERAGE_COOKIE) {
                $found = true;
                $cookie['Value'] = $value;
                break;
            }
        }
        unset($cookie);

        if (!$found) {
            $cookies[] = [
                'Name' => self::COVERAGE_COOKIE,
                'Value' => $value
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
                $alert = $this->module->webDriver->switchTo()->alert();
                $alert->getText();
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
            $error = null;
        }
        if (!empty($error)) {
            $this->module->resetCookie(self::COVERAGE_COOKIE_ERROR);
            throw new RemoteException($error);
        }
    }

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
        if (!str_contains($this->c3Access['http']['header'], $headerString)) {
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
}
