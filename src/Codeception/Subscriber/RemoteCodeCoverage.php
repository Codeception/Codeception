<?php
namespace Codeception\Subscriber;
use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RemoteCodeCoverage extends \Codeception\Subscriber\CodeCoverage implements EventSubscriberInterface
{
    protected $options = array();
    protected $enabled = false;
    protected $remote = false;

    protected $settings = array('enabled' => false, 'remote' => false, 'xdebug_session' => 'codeception');

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

        $this->module = $this->getRemoteConnectionModule();

        if (function_exists('xdebug_is_enabled')
            && xdebug_is_enabled()
            && ini_get('xdebug.remote_enable')
        ) {
            $this->module->_setCookie('XDEBUG_SESSION', $this->settings['xdebug_session']);
        }
    }

    public function beforeTest(\Codeception\Event\Test $e)
    {
        if (!$this->enabled or !$this->remote) return;
        $this->module->_setHeader('X-Codeception-CodeCoverage', $e->getTest()->toString());
    }

    public function afterSuite(\Codeception\Event\Suite $e)
    {
        if (!$this->enabled or !$this->remote) return;

        $suite = $e->getName();
        if ($this->options['xml']) $this->retrieveAndPrintXml($suite);
        if ($this->options['html']) $this->retrieveAndPrintHtml($suite);
    }

    protected function retrieveAndPrintHtml($suite)
    {
        $tempFile = str_replace('.', '', tempnam(sys_get_temp_dir(), 'C3')) . '.tar';
        file_put_contents($tempFile, $this->getRemoteCoverageFile($this->module, 'html'));

        $destDir = \Codeception\Configuration::logDir() . $suite . '.remote.codecoverage';

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
        $destFile = \Codeception\Configuration::logDir() . $suite . '.remote.codeception.xml';
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
        $this->enabled = $this->settings['enabled'];
        $this->remote = $this->settings['remote'];
    }

    static function getSubscribedEvents()
    {
        return array(
            'suite.after' => 'afterSuite',
            'suite.before' => 'beforeSuite',
            'test.before' => 'beforeTest',
        );
    }
}
