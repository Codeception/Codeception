<?php
namespace Codeception\Coverage\Subscriber;

use Codeception\CodeceptionEvents;
use Codeception\Configuration;
use Codeception\Coverage\SuiteSubscriber;
use Codeception\Coverage\Shared\C3Collect;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Exception\RemoteException;
use Codeception\Lib\WebInterface;

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
        'method' => "GET",
        'header' => ''
    ];

    /**
     * @var WebInterface
     */
    protected $module;

    static $events = [
        CodeceptionEvents::SUITE_BEFORE => 'beforeSuite',
        CodeceptionEvents::STEP_BEFORE  => 'beforeStep',
        CodeceptionEvents::STEP_AFTER   => 'afterStep',
        CodeceptionEvents::SUITE_AFTER => 'afterSuite',
    ];

    protected function isEnabled()
    {
        return $this->module and !$this->settings['remote'];
    }

    public function beforeSuite(SuiteEvent $e)
    {
        $this->module = $this->getServerConnectionModule();
        $this->applySettings($e->getSettings());
        if (!$this->isEnabled()) {
            return;
        }

        $this->suiteName = $e->getSuite()->baseName;

        if ($this->settings['remote_config']) {
            $this->addC3AccessHeader(self::COVERAGE_HEADER_CONFIG, $this->settings['remote_config']);
        }

        $knock = $this->c3Request('clear');
        if ($knock === false) {
            throw new RemoteException('
                CodeCoverage Error.
                Check the file "c3.php" is included in your application.
                We tried to access "/c3/report/clear" but this URI was not accessible.
                You can review actual error messages in c3tmp dir.
                '
            );
        }

    }

    public function beforeStep(StepEvent $e)
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
        $this->stopCoverageCollection();
    }

    public function afterSuite(SuiteEvent $e)
    {
        if (!$this->isEnabled()) {
            return;
        }

        if (!file_exists(Configuration::logDir() . 'c3tmp/codecoverage.serialized')) {
            if (file_exists(Configuration::logDir() . 'c3tmp/error.txt')) {
                throw new \RuntimeException(file_get_contents(Configuration::logDir() . 'c3tmp/error.txt'));
            }
            return;
        }

        $contents = file_get_contents(Configuration::logDir() . 'c3tmp/codecoverage.serialized');
        $coverage = @unserialize($contents);
        if ($coverage === false) {
            return;
        }
        $this->mergeToPrint($coverage);
    }

    protected function c3Request($action)
     {
         $this->addC3AccessHeader(self::COVERAGE_HEADER, 'remote-access');
         $context = stream_context_create(array('http' => $this->c3Access));
         $contents = file_get_contents($this->module->_getUrl() . '/c3/report/' . $action, false, $context);
         if ($contents === false) {
             $this->getRemoteError($http_response_header);
         }
         return $contents;
     }

     protected function startCoverageCollection($testName)
     {
         $cookie = [
             'CodeCoverage'        => $testName,
             'CodeCoverage_Suite'  => $this->suiteName,
             'CodeCoverage_Config' => $this->settings['remote_config']
         ];
         $this->module->setCookie(self::COVERAGE_COOKIE, json_encode($cookie));

         if (!method_exists($this->module, 'setHeader')) {
             return;
         }
         $this->module->setHeader(self::COVERAGE_HEADER, $testName);
         $this->module->setHeader(self::COVERAGE_HEADER_SUITE, $this->suiteName);
         if ($this->settings['remote_config']) {
             $this->module->setHeader(self::COVERAGE_HEADER_CONFIG, $this->settings['remote_config']);
         }
     }

    protected function stopCoverageCollection()
    {
        if ($error = $this->module->grabCookie(self::COVERAGE_COOKIE_ERROR)) {
            throw new RemoteException($error);
        }
        $this->module->resetCookie(self::COVERAGE_COOKIE_ERROR);
        $this->module->resetCookie(self::COVERAGE_COOKIE);

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
         $this->c3Access['header'] .= "$header: $value\r\n";
     }


}