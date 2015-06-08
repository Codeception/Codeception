<?php
namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ErrorHandler implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    static $events = [
        Events::SUITE_BEFORE => 'handle'
    ];

    /**
     * @var bool $stopped to keep shutdownHandler from possible looping.
     */
    private static $stopped = false;

    /**
     * @var int stores bitmask for errors
     */
    private $errorLevel = 'E_ALL & ~E_STRICT & ~E_DEPRECATED';

    public function handle(SuiteEvent $e)
    {
        $settings = $e->getSettings();
        if ($settings['error_level']) {
            $this->errorLevel = $settings['error_level'];
        }
        error_reporting(eval("return {$this->errorLevel};"));
        set_error_handler([$this, 'errorHandler']);
        register_shutdown_function([$this, 'shutdownHandler']);
    }

    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting
            return false;
        }

        if (strpos($errstr, 'Cannot modify header information') !== false) {
            return false;
        }

        throw new \PHPUnit_Framework_Exception($errstr, $errno);
    }

    public function shutdownHandler()
    {
        if (self::$stopped) {
            return;
        }
        self::$stopped = true;
        $error = error_get_last();
        if (!is_array($error)) {
            return;
        }
        if (error_reporting() === 0) {
            return;
        }
        // not fatal
        if ($error['type'] > 1) {
            return;
        }

        echo "\n\n\nFATAL ERROR. TESTS NOT FINISHED.\n";
        echo sprintf("%s \nin %s:%d\n", $error['message'], $error['file'], $error['line']);
    }
}
