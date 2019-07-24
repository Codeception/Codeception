<?php

namespace Codeception\Module\Async;

use SplFileInfo;
use SplFileObject;
use function clearstatcache;
use function error_get_last;
use function json_decode;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use function microtime;
use function usleep;
use const JSON_ERROR_NONE;
use const LOCK_EX;
use const LOCK_UN;

class IPC
{
    private $inputFilename;

    private $inputFileSize = 0;

    private $outputFilename;

    public function __construct($inputFilename, $outputFilename)
    {
        $this->inputFilename = $inputFilename;
        $this->outputFilename = $outputFilename;
    }

    private function waitSizeChange($filename, $initialSize, $timeoutInSeconds)
    {
        $t0 = microtime(true);
        $f = new SplFileInfo($filename);

        while (($newSize = $f->getSize()) === $initialSize) {
            if (microtime(true) - $t0 > $timeoutInSeconds) {
                die('read failed: timeout');
            }

            usleep(50000);
            clearstatcache(true, $filename);
        }
    }

    /**
     * @param SplFileObject $file
     * @param callable $callback
     * @return mixed
     */
    private function exclusive($file, $callback)
    {
        $file->flock(LOCK_EX)
        or die('flock failed: ' . error_get_last());

        $result = $callback($file);

        $file->flock(LOCK_UN)
        or die('flock failed: ' . error_get_last());

        return $result;
    }

    public function read()
    {
        $this->waitSizeChange($this->inputFilename, $this->inputFileSize, 3);

        return $this->exclusive(
            new SplFileObject($this->inputFilename, 'r'),
            /**
             * @param SplFileObject $file
             * @return mixed
             */
            function ($file) {
                $file->fseek($this->inputFileSize);
                $message = json_decode($file->fgets(), true);
                if ($message === null && json_last_error() !== JSON_ERROR_NONE) {
                    die('read failed: ' . json_last_error_msg());
                }
                $this->inputFileSize = $file->ftell();
                return $message;
            }
        );
    }

    public function write($message)
    {
        $this->exclusive(
            new SplFileObject($this->outputFilename, 'a+'),
            /**
             * @param SplFileObject $file
             */
            function ($file) use ($message) {
                $file->fwrite(json_encode($message) . "\n");
            }
        );
    }
}
