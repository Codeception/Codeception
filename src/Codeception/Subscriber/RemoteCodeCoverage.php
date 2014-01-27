<?php

namespace Codeception\Subscriber;

use Codeception\CodeceptionEvents;
use Codeception\Configuration;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Exception\RemoteException;
use Codeception\Module\PhpBrowser;
use Codeception\Util\FileSystem;
use Codeception\Util\RemoteInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RemoteCodeCoverage extends CodeCoverage implements EventSubscriberInterface
{
    protected $options = array();
    protected $enabled = false;
    protected $remote = false;
    protected $suiteName = "";

    protected $settings = array(
        'enabled'        => false,
        'remote'         => false,
        'xdebug_session' => 'codeception',
        'remote_config'  => ''
    );

    /**
     * @var RemoteInterface|PhpBrowser
     */
    protected $module = null;

    function __construct($options)
    {
        $this->options = $options;
    }

    public function beforeSuite(SuiteEvent $e)
    {
        $this->applySettings($e->getSettings());
        if (! $this->enabled && ! $this->remote) {
            return;
        }

        $this->suiteName = $e->getSuite()->getName();
        $this->module    = $this->getRemoteConnectionModule();
        if (! $this->module) {
            return;
        }

        if ($this->settings['remote_config']) {
            $this->addHeader('X-Codeception-CodeCoverage-Config', $this->settings['remote_config']);
        }

        $knock = $this->getRemoteCoverageFile($this->module, 'clear');
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
        if (! $this->module) {
            return;
        }

        $cookie = array(
            'CodeCoverage'        => $e->getTest()->getName(),
            'CodeCoverage_Suite'  => $this->suiteName,
            'CodeCoverage_Config' => $this->settings['remote_config']
        );
        $this->module->setCookie('CODECEPTION_CODECOVERAGE', json_encode($cookie));

        if (! method_exists($this->module, '_setHeader')) {
            return;
        }
        $this->module->_setHeader('X-Codeception-CodeCoverage', $e->getTest()->getName());
        $this->module->_setHeader('X-Codeception-CodeCoverage-Suite', $this->suiteName);
        if ($this->settings['remote_config']) {
            $this->module->_setHeader('X-Codeception-CodeCoverage-Config', $this->settings['remote_config']);
        }
    }

    public function afterStep(StepEvent $e)
    {
        if (! $this->module) {
            return;
        }
        if ($error = $this->module->grabCookie('CODECEPTION_CODECOVERAGE_ERROR')) {
            throw new RemoteException($error);
        }
        $this->module->resetCookie('CODECEPTION_CODECOVERAGE_ERROR');
        $this->module->resetCookie('CODECEPTION_CODECOVERAGE');
    }

    public function afterSuite(SuiteEvent $e)
    {
        if (! $this->module) {
            return;
        }
        if (! $this->remote) {
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

        $destDir = Configuration::logDir() . $suite . '.remote.coverage';
        if (is_dir($destDir)) {
            FileSystem::doEmptyDir($destDir);
        } else {
            mkdir($destDir, 0777, true);
        }

        $phar = new \PharData($tempFile);
        $phar->extractTo($destDir);

        unlink($tempFile);
    }

    protected function retrieveAndPrintXml($suite)
    {
        $destFile = Configuration::logDir() . $suite . '.remote.coverage.xml';
        file_put_contents($destFile, $this->getRemoteCoverageFile($this->module, 'clover'));
    }

    protected function applySettings($settings)
    {
        $keys = array_keys($this->settings);
        foreach ($keys as $key) {
            if (isset($settings['coverage'][$key])) {
                $this->settings[$key] = $settings['coverage'][$key];
            }
        }
        $this->enabled = $this->settings['enabled'] && function_exists('xdebug_is_enabled');
        $this->remote  = $this->settings['remote'];
    }

    static public function getSubscribedEvents()
    {
        return array(
            CodeceptionEvents::SUITE_AFTER  => 'afterSuite',
            CodeceptionEvents::SUITE_BEFORE => 'beforeSuite',
            CodeceptionEvents::STEP_BEFORE  => 'beforeStep',
            CodeceptionEvents::STEP_AFTER   => 'afterStep',
        );
    }
}
