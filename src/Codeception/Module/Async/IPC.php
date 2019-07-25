<?php

namespace Codeception\Module\Async;

use Exception;
use SplFileObject;
use function array_key_exists;
use function clearstatcache;
use function error_get_last;
use function filesize;
use function is_array;
use function is_string;
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
    const CHANNEL_KEY = 'channel';
    const PAYLOAD_KEY = 'payload';

    /**
     * @var string
     */
    private $inputFilename;

    /**
     * @var int
     */
    private $inputFileSize = 0;

    /**
     * @var string
     */
    private $outputFilename;

    /**
     * @var array[][]
     */
    private $channels = [];

    /**
     * @param string $inputFilename
     * @param string $outputFilename
     */
    public function __construct($inputFilename, $outputFilename)
    {
        $this->inputFilename = $inputFilename;
        $this->outputFilename = $outputFilename;
    }

    /**
     * @param string $filename
     * @param int $lastKnownSize
     * @return bool
     */
    private function isSizeChanged($filename, $lastKnownSize)
    {
        clearstatcache(true, $filename);
        return filesize($filename) !== $lastKnownSize;
    }

    /**
     * @param string $filename
     * @param int $lastKnownSize
     * @param float $timeoutInSeconds
     * @throws Exception
     */
    private function waitSizeChange($filename, $lastKnownSize, $timeoutInSeconds)
    {
        $t0 = microtime(true);
        while (!$this->isSizeChanged($filename, $lastKnownSize)) {
            if (microtime(true) - $t0 > $timeoutInSeconds) {
                throw new Exception('read failed: timeout');
            }
            usleep(50000);
        }
    }

    /**
     * @param SplFileObject $file
     * @param callable $callback
     * @return mixed
     * @throws Exception
     */
    private function exclusive($file, $callback)
    {
        if (false === ($file->flock(LOCK_EX))) {
            throw new Exception('flock failed: ' . error_get_last());
        }

        $result = $callback($file);

        if (false === ($file->flock(LOCK_UN))) {
            throw new Exception('flock failed: ' . error_get_last());
        }

        return $result;
    }

    /**
     * @param string $channel
     * @param mixed $payload
     * @return string
     * @throws Exception
     */
    private function formatMessage($channel, $payload)
    {
        $string = json_encode([self::CHANNEL_KEY => $channel, self::PAYLOAD_KEY => $payload]);

        if ($string === false) {
            throw new Exception('failed formatting message: ' . json_last_error_msg());
        }

        return $string;
    }

    /**
     * @param string $string
     * @return mixed
     * @throws Exception
     */
    private function parseMessage($string)
    {
        $message = json_decode($string, true);

        if ($message === null
            && json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed parsing message: ' . json_last_error_msg());
        }

        if (!is_array($message)
            || !array_key_exists(self::CHANNEL_KEY, $message)
            || !is_string($message[self::CHANNEL_KEY])
            || !array_key_exists(self::PAYLOAD_KEY, $message)) {
            throw new Exception('failed parsing message: malformed message');
        }

        return $message;
    }

    /**
     * @throws Exception
     */
    private function readAll()
    {
        while ($this->isSizeChanged($this->inputFilename, $this->inputFileSize)) {
            $this->exclusive(
                new SplFileObject($this->inputFilename, 'r'),
                /**
                 * @param SplFileObject $file
                 */
                function ($file) {
                    $file->fseek($this->inputFileSize);
                    $message = $this->parseMessage($file->fgets());
                    $this->inputFileSize = $file->ftell();
                    $this->channels[$message[self::CHANNEL_KEY]][] = $message[self::PAYLOAD_KEY];
                }
            );
        }
    }

    /**
     * @param string $channel
     * @return mixed
     * @throws Exception
     */
    public function read($channel)
    {
        if (empty($this->channels[$channel])) {
            $this->waitSizeChange($this->inputFilename, $this->inputFileSize, 3);
            $this->readAll();
            if (empty($this->channels[$channel])) {
                throw new Exception('no messages on channel "' . $channel . '"');
            }
        }

        return array_shift($this->channels[$channel]);
    }

    /**
     * @param string $channel
     * @param mixed $payload
     * @throws Exception
     */
    public function write($channel, $payload)
    {
        $this->exclusive(
            new SplFileObject($this->outputFilename, 'a+'),
            /**
             * @param SplFileObject $file
             */
            function ($file) use ($channel, $payload) {
                $file->fwrite($this->formatMessage($channel, $payload) . "\n");
            }
        );
    }
}
