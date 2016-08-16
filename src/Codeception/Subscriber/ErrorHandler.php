<?php
namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Lib\Notification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ErrorHandler implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    public static $events = [
        Events::SUITE_BEFORE => 'handle'
    ];

    /**
     * @var bool $stopped to keep shutdownHandler from possible looping.
     */
    private static $stopped = false;

    private $deprecationsInstalled = false;
    private $oldHandler;

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
        // We must register shutdown function before deprecation error handler to restore previous error handler
        // and silence DeprecationErrorHandler yelling about 'THE ERROR HANDLER HAS CHANGED!'
        register_shutdown_function([$this, 'shutdownHandler']);
        $this->registerDeprecationErrorHandler();
        $this->oldHandler = set_error_handler([$this, 'errorHandler']);
    }

    public function errorHandler($errno, $errstr, $errfile, $errline, $context)
    {
        if (E_USER_DEPRECATED === $errno) {
            $this->handleDeprecationError($errno, $errstr, $errfile, $errline, $context);
            return;
        }

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
        if ($this->deprecationsInstalled) {
            restore_error_handler();
        }

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

    private function registerDeprecationErrorHandler()
    {
        if (class_exists('\Symfony\Bridge\PhpUnit\DeprecationErrorHandler')) {
            // DeprecationErrorHandler only will be installed if array('PHPUnit_Util_ErrorHandler', 'handleError')
            // is installed or no other error handlers are installed.
            // So we will remove Symfony\Component\Debug\ErrorHandler if it's installed.
            $old = set_error_handler('var_dump');
            restore_error_handler();

            if ($old
                && is_array($old)
                && count($old) > 0
                && is_object($old[0])
                && get_class($old[0]) === 'Symfony\Component\Debug\ErrorHandler'
            ) {
                restore_error_handler();
            }

            $this->deprecationsInstalled = true;
            \Symfony\Bridge\PhpUnit\DeprecationErrorHandler::register(getenv('SYMFONY_DEPRECATIONS_HELPER'));
        }
    }

    private function handleDeprecationError($type, $message, $file, $line, $context)
    {
        if ($this->deprecationsInstalled && $this->oldHandler) {
            call_user_func($this->oldHandler, $type, $message, $file, $line, $context);
            return;
        }
        Notification::deprecate("$message", "$file:$line");
    }
}
