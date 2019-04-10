<?php
namespace Codeception\Coverage\Subscriber;

use Codeception\Configuration;
use Codeception\Event\SuiteEvent;
use Codeception\Util\FileSystem;

/**
 * When collecting code coverage on remote server
 * data is retrieved over HTTP and not merged with the local code coverage results.
 *
 * Class RemoteServer
 * @package Codeception\Coverage\Subscriber
 */
class RemoteServer extends LocalServer
{
    public function isEnabled()
    {
        return $this->module and $this->settings['remote'] and $this->settings['enabled'];
    }

    public function beforeSuite(SuiteEvent $e)
    {
        parent::beforeSuite($e);

        if (!$this->isEnabled()) {
            return;
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

    public function afterSuite(SuiteEvent $e)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $suite = strtr($e->getSuite()->getName(), ['\\' => '.']);
        if ($this->options['coverage-xml']) {
            $this->retrieveAndPrintXml($suite);
        }
        if ($this->options['coverage-html']) {
            $this->retrieveAndPrintHtml($suite);
        }
        if ($this->options['coverage-crap4j']) {
            $this->retrieveAndPrintCrap4j($suite);
        }
        if ($this->options['coverage-phpunit']) {
            $this->retrieveAndPrintPHPUnit($suite);
        }
    }

    protected function retrieveAndPrintHtml($suite)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'C3') . '.tar';
        file_put_contents($tempFile, $this->c3Request('html'));

        $destDir = Configuration::outputDir() . $suite . '.remote.coverage';
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
        $destFile = Configuration::outputDir() . $suite . '.remote.coverage.xml';
        file_put_contents($destFile, $this->c3Request('clover'));
    }

    protected function retrieveAndPrintCrap4j($suite)
    {
        $destFile = Configuration::outputDir() . $suite . '.remote.crap4j.xml';
        file_put_contents($destFile, $this->c3Request('crap4j'));
    }

    protected function retrieveAndPrintPHPUnit($suite)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'C3') . '.tar';
        file_put_contents($tempFile, $this->c3Request('phpunit'));

        $destDir = Configuration::outputDir() . $suite . '.remote.coverage-phpunit';
        if (is_dir($destDir)) {
            FileSystem::doEmptyDir($destDir);
        } else {
            mkdir($destDir, 0777, true);
        }

        $phar = new \PharData($tempFile);
        $phar->extractTo($destDir);

        unlink($tempFile);
    }
}
