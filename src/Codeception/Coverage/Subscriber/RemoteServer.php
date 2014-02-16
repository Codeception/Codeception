<?php
namespace Codeception\Coverage\Subscriber;

use Codeception\Configuration;
use Codeception\Coverage\Shared\C3Collect;
use Codeception\Event\SuiteEvent;
use Codeception\Util\FileSystem;

/**
 * When collecting code coverage on remote server result
 * data is retrieved over HTTP and not merged with the local code coverage results.
 *
 * Class RemoteServer
 * @package Codeception\Coverage\Subscriber
 */
class RemoteServer extends LocalServer
{
    public function isEnabled()
    {
        return $this->getServerConnectionModule() and $this->settings['remote'];
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
        file_put_contents($tempFile, $this->c3Request('html'));

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
        file_put_contents($destFile, $this->c3Request('clover'));
    }

}
