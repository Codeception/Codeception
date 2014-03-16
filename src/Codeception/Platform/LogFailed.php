<?php
namespace Codeception\Platform;

use Codeception\Event\PrintResultEvent;
use Codeception\Events;
use Codeception\TestCase;

/**
 * Saves failed tests into tests/log/failed in order to rerun failed tests.
 */
class LogFailed extends Extension
{
    static $events = [
      Events::RESULT_PRINT_AFTER => 'saveFailed'
    ];

    protected $config = ['file' => 'failed'];

    public function saveFailed(PrintResultEvent $e)
    {
        $file = $this->getLogDir().$this->config['file'];
        $result = $e->getResult();
        if ($result->wasSuccessful()) {
            if (is_file($file)) {
                unlink($file);
            }
            return;
        }
        $output = [];
        foreach ($result->failures() as $fail) {
            $output[] = $this->localizePath(TestCase::getTestFullName($fail->failedTest()));
        }
        file_put_contents($file, implode("\n", $output));
    }

    protected function localizePath($path)
    {
        $root = realpath($this->getRootDir()).DIRECTORY_SEPARATOR;
        if (substr($path, 0, strlen($root)) == $root) {
            return substr($path, strlen($root));
        }
        return $path;
    }

} 