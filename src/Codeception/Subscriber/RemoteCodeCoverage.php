<?php
namespace Codeception\Subscriber;
use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RemoteCodeCoverage extends \Codeception\Subscriber\CodeCoverage implements EventSubscriberInterface
{
    protected $options = array();
    protected $enabled = false;
    protected $module = null;

    function __construct($options)
    {
        $this->options = $options;
    }

    public function afterSuite(\Codeception\Event\Suite $e)
    {
        $this->applySettings($e->getSettings());
        if (!$this->enabled or !$this->remote) return;

        $this->module = $this->getRemoteConnectionModule();

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
            if (isset($config['coverage'][$key])) {
                $this->settings[$key] = $settings['coverage'][$key];
            }
        }
    }

    static function getSubscribedEvents()
    {
        return array(
            'suite.after' => 'afterSuite',
        );
    }
}
