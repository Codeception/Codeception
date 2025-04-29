<?php

declare(strict_types=1);

namespace Codeception\Coverage\Subscriber;

use Codeception\Configuration;
use Codeception\Event\SuiteEvent;
use Codeception\Lib\Interfaces\Web;
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
        return $this->module instanceof Web && $this->settings['remote'] && $this->settings['enabled'];
    }

    public function afterSuite(SuiteEvent $event): void
    {
        if (!$this->isEnabled()) {
            return;
        }
        $suite = strtr($event->getSuite()->getName(), ['\\' => '.']);

        if ($this->options['coverage-xml']) {
            $this->retrieveAndPrint('clover', $suite, '.remote.coverage.xml');
        }
        if ($this->options['coverage-html']) {
            $this->retrieveToTempFileAndPrint('html', $suite, '.remote.coverage');
        }
        if ($this->options['coverage-crap4j']) {
            $this->retrieveAndPrint('crap4j', $suite, '.remote.crap4j.xml');
        }
        if ($this->options['coverage-cobertura']) {
            $this->retrieveAndPrint('cobertura', $suite, '.remote.cobertura.xml');
        }
        if ($this->options['coverage-phpunit']) {
            $this->retrieveToTempFileAndPrint('phpunit', $suite, '.remote.coverage-phpunit');
        }
    }

    protected function retrieveAndPrint(string $type, string $suite, string $extension): void
    {
        $destFile = Configuration::outputDir() . $suite . $extension;
        file_put_contents($destFile, $this->c3Request($type));
    }

    protected function retrieveToTempFileAndPrint(string $type, string $suite, string $extension): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'C3') . '.tar';
        file_put_contents($tempFile, $this->c3Request($type));

        $destDir = Configuration::outputDir() . $suite . $extension;
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
