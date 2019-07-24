<?php

namespace Codeception\Module\Async;

use function error_get_last;
use function filesize;
use function fopen;
use function fseek;
use function ftell;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use function microtime;
use const JSON_ERROR_NONE;

abstract class AbstractController
{
    private $inputFilename;

    private $inputFileSize = 0;

    private $outputFilename;

    public function __construct($inputFilename, $outputFilename)
    {
        $this->inputFilename = $inputFilename;
        $this->outputFilename = $outputFilename;
    }

    public function read()
    {
        $timeout = 3;
        $t0 = microtime(true);

        while (filesize($this->inputFilename) === $this->inputFileSize) {
            if (microtime(true) - $t0 > $timeout) {
                die('read failed: timeout');
            }

            usleep(50000);
            clearstatcache(true, $this->inputFilename);
        }

        $f = fopen($this->inputFilename, 'r')
        or die('fopen failed: ' . error_get_last());
        fseek($f, $this->inputFileSize);
        $res = fscanf($f, "size=%d\n", $length);
        if ($res !== 1) {
            die('read failed: invalid pattern');
        }
        $packet = fread($f, $length);
        $this->inputFileSize = ftell($f) + strlen("\n");
        fclose($f);
        $message = json_decode($packet, true);
        if ($message === null && json_last_error() !== JSON_ERROR_NONE) {
            die('read failed: ' . json_last_error_msg());
        }
        return $message;
    }

    public function write($message)
    {
        $f = fopen($this->outputFilename, 'a+')
        or die('fopen failed: ' . error_get_last());
        $packet = json_encode($message);
        fwrite($f, 'size=' . strlen($packet) . "\n" . $packet . "\n");
        fclose($f);
    }
}
