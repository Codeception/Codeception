<?php

declare(strict_types=1);

namespace Codeception\Coverage\Subscriber;

use Codeception\Configuration;
use Codeception\Event\SuiteEvent;
use Codeception\Util\FileSystem;
use PharData;

use function file_put_contents;
use function is_dir;
use function mkdir;
use function strtr;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * When collecting code coverage on remote server
 * data is retrieved over HTTP and not merged with the local code coverage results.
 *
 * Class RemoteServer
 * @package Codeception\Coverage\Subscriber
 */
class RemoteServer extends LocalServer
{
    public function isEnabled(): bool
    {
        return $this->module && $this->settings['remote'] && $this->settings['enabled'];
    }

    public function afterSuite(SuiteEvent $event): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $suite = strtr($event->getSuite()->getName(), ['\\' => '.']);
        if ($this->options['coverage-xml']) {
            $this->retrieveAndPrintXml($suite);
        }
        if ($this->options['coverage-html']) {
            $this->retrieveAndPrintHtml($suite);
        }
        if ($this->options['coverage-crap4j']) {
            $this->retrieveAndPrintCrap4j($suite);
        }
        if ($this->options['coverage-cobertura']) {
            $this->retrieveAndPrintCobertura($suite);
        }
        if ($this->options['coverage-phpunit']) {
            $this->retrieveAndPrintPHPUnit($suite);
        }
    }

    protected function retrieveAndPrintHtml(string $suite): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'C3') . '.tar';
        file_put_contents($tempFile, $this->c3Request('html'));

        $destDir = Configuration::outputDir() . $suite . '.remote.coverage';
        if (is_dir($destDir)) {
            FileSystem::doEmptyDir($destDir);
        } else {
            mkdir($destDir, 0777, true);
        }

        $pharData = new PharData($tempFile);
        $pharData->extractTo($destDir);

        unlink($tempFile);
    }

    protected function retrieveAndPrintXml(string $suite): void
    {
        $destFile = Configuration::outputDir() . $suite . '.remote.coverage.xml';
        file_put_contents($destFile, $this->c3Request('clover'));
    }

    protected function retrieveAndPrintCrap4j(string $suite): void
    {
        $destFile = Configuration::outputDir() . $suite . '.remote.crap4j.xml';
        file_put_contents($destFile, $this->c3Request('crap4j'));
    }

    protected function retrieveAndPrintCobertura(string $suite): void
    {
        $destFile = Configuration::outputDir() . $suite . '.remote.cobertura.xml';
        file_put_contents($destFile, $this->c3Request('cobertura'));
    }

    protected function retrieveAndPrintPHPUnit(string $suite): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'C3') . '.tar';
        file_put_contents($tempFile, $this->c3Request('phpunit'));

        $destDir = Configuration::outputDir() . $suite . '.remote.coverage-phpunit';
        if (is_dir($destDir)) {
            FileSystem::doEmptyDir($destDir);
        } else {
            mkdir($destDir, 0777, true);
        }

        $pharData = new PharData($tempFile);
        $pharData->extractTo($destDir);

        unlink($tempFile);
    }
}
