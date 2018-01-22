<?php
namespace Codeception\Coverage\Subscriber;

use Codeception\Configuration;
use Codeception\Coverage\SuiteSubscriber;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Exception\ModuleException;
use Codeception\Exception\RemoteException;

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
    const COVERAGE_HEADER = 'X-Codeception-CodeCoverage';
    const COVERAGE_HEADER_ERROR = 'X-Codeception-CodeCoverage-Error';
    const COVERAGE_HEADER_CONFIG = 'X-Codeception-CodeCoverage-Config';
    const COVERAGE_HEADER_SUITE = 'X-Codeception-CodeCoverage-Suite';

    // cookie names
    const COVERAGE_COOKIE = 'CODECEPTION_CODECOVERAGE';
    const COVERAGE_COOKIE_ERROR = 'CODECEPTION_CODECOVERAGE_ERROR';

    protected $suiteName;
    protected $c3Access = [
        'http' => [
            'method' => "GET",
            'header' => ''
        ]
    ];

    /**
     * @var \Codeception\Lib\Interfaces\Web
     */
    protected $module;

    public static $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::TEST_BEFORE  => 'beforeTest',
        Events::STEP_AFTER   => 'afterStep',
        Events::SUITE_AFTER  => 'afterSuite',
    ];

    protected function isEnabled()
    {
        return $this->module && !$this->settings['remote'] && $this->settings['enabled'];
    }

    public function beforeSuite(SuiteEvent $e)
    {
        $this->module = $this->getServerConnectionModule($e->getSuite()->getModules());
        $this->applySettings($e->getSettings());
        if (!$this->isEnabled()) {
            return;
        }

        $this->suiteName = $e->getSuite()->getBaseName();

        if ($this->settings['remote_config']) {
            $this->addC3AccessHeader(self::COVERAGE_HEADER_CONFIG, $this->settings['remote_config']);
        }

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

    public function beforeTest(TestEvent $e)
    {
        if (!$this->isEnabled()) {
            return;
        }
        $this->startCoverageCollection($e->getTest()->getName());
    }

    public function afterStep(StepEvent $e)
    {
        if (!$this->isEnabled()) {
            return;
        }
        $this->fetchErrors();
    }

    public function afterSuite(SuiteEvent $e)
    {
        if (!$this->isEnabled()) {
            return;
        }
        $coverageFile = Configuration::outputDir() . 'c3tmp/codecoverage.serialized';

        $retries = 5;
        while (!file_exists($coverageFile) && --$retries >= 0) {
            usleep(0.5 * 1000000); // 0.5 sec
        }

        if (!file_exists($coverageFile)) {
            if (file_exists(Configuration::outputDir() . 'c3tmp/error.txt')) {
                throw new \RuntimeException(file_get_contents(Configuration::outputDir() . 'c3tmp/error.txt'));
            }
            return;
        }

        $contents = file_get_contents($coverageFile);
        $coverage = @unserialize($contents);
        if ($coverage === false) {
            return;
        }
        $this->mergeToPrint($coverage);
    }

    protected function c3Request($action)
    {
        $this->addC3AccessHeader(self::COVERAGE_HEADER, 'remote-access');
        $context = stream_context_create($this->c3Access);
        $c3Url = $this->settings['c3_url'] ? $this->settings['c3_url'] : $this->module->_getUrl();
        $contents = file_get_contents($c3Url . '/c3/report/' . $action, false, $context);

        $okHeaders = array_filter(
            $http_response_header,
            function ($h) {
                return preg_match('~^HTTP(.*?)\s200~', $h);
            }
        );
        if (empty($okHeaders)) {
            throw new RemoteException("Request was not successful. See response header: " . $http_response_header[0]);
        }
        if ($contents === false) {
            $this->getRemoteError($http_response_header);
        }
        return $contents;
    }

    protected function startCoverageCollection($testName)
    {
        $value = [
            'CodeCoverage'        => $testName,
            'CodeCoverage_Suite'  => $this->suiteName,
            'CodeCoverage_Config' => $this->settings['remote_config']
        ];
        $value = json_encode($value);

        if ($this->module instanceof \Codeception\Module\WebDriver) {
            $this->module->amOnPage('/');
        }

        $c3Url = parse_url($this->settings['c3_url'] ? $this->settings['c3_url'] : $this->module->_getUrl());

        // we need to separate coverage cookies by host; we can't separate cookies by port.
        $c3Host = isset($c3Url['host']) ? $c3Url['host'] : 'localhost';

        $this->module->setCookie(self::COVERAGE_COOKIE, $value, ['domain' => $c3Host]);

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
        if (!$found) {
            $cookies[] = [
                'Name' => self::COVERAGE_COOKIE,
                'Value' => $value
            ];
        }

        $this->module->_setConfig(['cookies' => $cookies]);
    }

    protected function fetchErrors()
    {
        try {
            $error = $this->module->grabCookie(self::COVERAGE_COOKIE_ERROR);
        } catch (ModuleException $e) {
            // when a new session is started we can't get cookies because there is no
            // current page, but there can be no code coverage error either
            $error = null;
        }
        if (!empty($error)) {
            $this->module->resetCookie(self::COVERAGE_COOKIE_ERROR);
            throw new RemoteException($error);
        }
    }

    protected function getRemoteError($headers)
    {
        foreach ($headers as $header) {
            if (strpos($header, self::COVERAGE_HEADER_ERROR) === 0) {
                throw new RemoteException($header);
            }
        }
    }

    protected function addC3AccessHeader($header, $value)
    {
        $headerString = "$header: $value\r\n";
        if (strpos($this->c3Access['http']['header'], $headerString) === false) {
            $this->c3Access['http']['header'] .= $headerString;
        }
    }

    protected function applySettings($settings)
    {
        parent::applySettings($settings);
        if (isset($settings['coverage']['remote_context_options'])) {
            $this->c3Access = array_replace_recursive($this->c3Access, $settings['coverage']['remote_context_options']);
        }
    }
}
