<?php
namespace Codeception\Coverage\Subscriber;

use Codeception\CodeceptionEvents;
use Codeception\Coverage\C3Connector;
use Codeception\Coverage\SuiteSubscriber;
use Codeception\Event\SuiteEvent;
use Codeception\Event\StepEvent;
use Codeception\Util\RemoteInterface;

class RemoteServer extends SuiteSubscriber
{
    use C3Connector;

    protected $suite_name = "";

    static $events = [
        CodeceptionEvents::SUITE_BEFORE => 'beforeSuite',
        CodeceptionEvents::SUITE_AFTER  => 'afterSuite',
        CodeceptionEvents::STEP_BEFORE  => 'beforeStep',
        CodeceptionEvents::STEP_AFTER   => 'afterStep',
    ];

    public function isEnabled()
    {
        return $this->getServerConnectionModule() and $this->settings['remote'];
    }

    public function beforeSuite(SuiteEvent $e)
    {
        $this->applySettings($e->getSettings());
        if (!$this->isEnabled()) {
            return;
        }

        $this->suite_name = $e->getSuite()->baseName;

        if ($this->settings['remote_config']) {
            $this->addHeader(COVERAGE_HEADER_CONFIG, $this->settings['remote_config']);
        }

        $knock = $this->c3Request($this->getServerConnectionModule()->_getUrl(), 'clear');
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

        $cookie = array(
            'CodeCoverage'        => $e->getTest()->getName(),
            'CodeCoverage_Suite'  => $this->suite_name,
            'CodeCoverage_Config' => $this->settings['remote_config']
        );
        $this->module->setCookie(COVERAGE_COOKIE, json_encode($cookie));

        if (!method_exists($this->module, '_setHeader')) {
            return;
        }
        $this->module->_setHeader(COVERAGE_HEADER, $e->getTest()->getName());
        $this->module->_setHeader(COVERAGE_HEADER_SUITE, $this->suite_name);
        if ($this->settings['remote_config']) {
            $this->module->_setHeader(COVERAGE_HEADER_CONFIG, $this->settings['remote_config']);
        }
    }

    public function afterStep(StepEvent $e)
    {
        if (!$this->isEnabled()) {
            return;
        }

        if ($error = $this->module->grabCookie(COVERAGE_COOKIE_ERROR)) {
            throw new RemoteException($error);
        }
        $this->module->resetCookie(COVERAGE_COOKIE_ERROR);
        $this->module->resetCookie(COVERAGE_COOKIE);
    }

    public function afterSuite(SuiteEvent $e)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $suite = $e->getSuite()->getName();
        if ($this->options['xml']) {
            $this->retrieveAndPrintXml($suite);
        }
        if ($this->options['html']) {
            $this->retrieveAndPrintHtml($suite);
        }
    }

    protected function retrieveAndPrintHtml($suite)
    {
        $tempFile = str_replace('.', '', tempnam(sys_get_temp_dir(), 'C3')) . '.tar';
        file_put_contents($tempFile, $this->getRemoteCoverageFile($this->module, 'html'));

        $destDir = \Codeception\Configuration::logDir() . $suite . '.remote.coverage';
        if (is_dir($destDir)) {
            \Codeception\Util\FileSystem::doEmptyDir($destDir);
        } else {
            mkdir($destDir, 0777, true);
        }

        $phar = new \PharData($tempFile);
        $phar->extractTo($destDir);

        unlink($tempFile);
    }

    protected function retrieveAndPrintXml($suite)
    {
        $destFile = \Codeception\Configuration::logDir() . $suite . '.remote.coverage.xml';
        file_put_contents($destFile, $this->getRemoteCoverageFile($this->module, 'clover'));
    }

}
