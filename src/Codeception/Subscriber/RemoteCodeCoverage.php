<?php

namespace Codeception\Subscriber;

use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RemoteCodeCoverage extends \Codeception\Subscriber\CodeCoverage implements EventSubscriberInterface
{
    protected $options = array();
    protected $enabled = false;
    protected $remote = false;
    protected $suite_name = "";

    protected $settings = array(
        'enabled'        => false,
        'remote'         => false,
        'xdebug_session' => 'codeception',
        'remote_config'  => ''
    );

    /**
     * @var \Codeception\Util\RemoteInterface
     */
    protected $module = null;

    function __construct($options)
    {
        $this->options = $options;
    }

    public function beforeSuite(\Codeception\Event\Suite $e)
    {
        $this->applySettings($e->getSettings());
        if (!$this->enabled) return;

        $this->suite_name = $e->getSuite()->getName();
        $this->module = $this->getRemoteConnectionModule();
        if (!$this->module) return;

        if ($this->settings['remote_config']) {
            $this->addHeader('X-Codeception-CodeCoverage-Config', $this->settings['remote_config']);
        }

        $knock = $this->getRemoteCoverageFile($this->module, 'clear');
        if ($knock === false) {
            throw new \Codeception\Exception\RemoteException('
                CodeCoverage Error.
                Check the file "c3.php" is included in your application.
                We tried to access "/c3/report/clear" but this URI was not accessible.
                You can review actual error messages in c3tmp dir.
                '
            );
        }

    }

    public function beforeStep(\Codeception\Event\Step $e)
    {
        if (!$this->module) return;

        $cookie = array(
            'CodeCoverage' => $e->getTest()->getName(),
            'CodeCoverage_Suite' => $this->suite_name,
            'CodeCoverage_Config' => $this->settings['remote_config']
        );
        $this->module->setCookie('CODECEPTION_CODECOVERAGE', json_encode($cookie));

        if (!method_exists($this->module, '_setHeader')) return;
        $this->module->_setHeader('X-Codeception-CodeCoverage', $e->getTest()->getName());
        $this->module->_setHeader('X-Codeception-CodeCoverage-Suite', $this->suite_name);
        if ($this->settings['remote_config']) {
            $this->module->_setHeader('X-Codeception-CodeCoverage-Config', $this->settings['remote_config']);
        }
    }

    public function afterStep(\Codeception\Event\Step $e)
    {
        if (!$this->module) return;
        if ($error  = $this->module->grabCookie('CODECEPTION_CODECOVERAGE_ERROR')) {
            throw new \Codeception\Exception\RemoteException($error);
        }
        $this->module->resetCookie('CODECEPTION_CODECOVERAGE_ERROR');
        $this->module->resetCookie('CODECEPTION_CODECOVERAGE');
    }

    public function afterSuite(\Codeception\Event\Suite $e)
    {
        if (!$this->module) return;
        if (!$this->remote) return;

        $suite = $e->getSuite()->getName();
        if ($this->options['xml']) $this->retrieveAndPrintXml($suite);
        if ($this->options['html']) $this->retrieveAndPrintHtml($suite);
    }

    protected function retrieveAndPrintHtml($suite)
    {
        $tempFile = str_replace('.', '', tempnam(sys_get_temp_dir(), 'C3')) . '.tar';
        file_put_contents($tempFile, $this->getRemoteCoverageFile($this->module, 'html'));

        $destDir = \Codeception\Configuration::logDir() . $suite . '.remote.coverage';

        if (!is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        } else {
            \Codeception\Util\FileSystem::doEmptyDir($destDir);
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

    protected function applySettings($settings)
    {
        $keys = array_keys($this->settings);
        foreach ($keys as $key) {
            if (isset($settings['coverage'][$key])) {
                $this->settings[$key] = $settings['coverage'][$key];
            }
        }
        $this->enabled = $this->settings['enabled']
                    && function_exists('xdebug_is_enabled');

        $this->remote = $this->settings['remote'];
    }

    static function getSubscribedEvents()
    {
        return array(
            'suite.after' => 'afterSuite',
            'suite.before' => 'beforeSuite',
            'step.before' => 'beforeStep',
            'step.after' => 'afterStep',
        );
    }
}
