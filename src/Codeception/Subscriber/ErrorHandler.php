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
        Events::SUITE_BEFORE => 'handle',
        Events::SUITE_AFTER  => 'onFinish'
    ];

    /**
     * @var bool $stopped to keep shutdownHandler from possible looping.
     */
    private $stopped = false;

    /**
     * @var bool $initialized to avoid double error handler substitution
     */
    private $initialized = false;

    private $deprecationsInstalled = false;
    private $oldHandler;

    private $suiteFinished = false;

    /**
     * @var int stores bitmask for errors
     */
    private $errorLevel;

    public function __construct()
    {
        $this->errorLevel = E_ALL & ~E_STRICT & ~E_DEPRECATED;
    }

    public function onFinish(SuiteEvent $e)
    {
        $this->suiteFinished = true;
    }

    public function handle(SuiteEvent $e)
    {
        $settings = $e->getSettings();
        if ($settings['error_level']) {
            $this->errorLevel = eval("return {$settings['error_level']};");
        }
        error_reporting($this->errorLevel);

        if ($this->initialized) {
            return;
        }
        // We must register shutdown function before deprecation error handler to restore previous error handler
        // and silence DeprecationErrorHandler yelling about 'THE ERROR HANDLER HAS CHANGED!'
        register_shutdown_function([$this, 'shutdownHandler']);
        $this->registerDeprecationErrorHandler();
        $this->oldHandler = set_error_handler([$this, 'errorHandler']);
        $this->initialized = true;
    }

    public function errorHandler($errno, $errstr, $errfile, $errline, $context = array())
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

        $relativePath = codecept_relative_path($errfile);
        throw new \PHPUnit\Framework\Exception("$errstr at $relativePath:$errline", $errno);
    }

    public function shutdownHandler()
    {
        if ($this->deprecationsInstalled) {
            restore_error_handler();
        }

        if ($this->stopped) {
            return;
        }
        $this->stopped = true;
        $error = error_get_last();

        if (!$this->suiteFinished && (
            $error === null || !in_array($error['type'], [E_ERROR, E_COMPILE_ERROR, E_CORE_ERROR])
        )) {
            echo "\n\n\nCOMMAND DID NOT FINISH PROPERLY.\n";
            exit(255);
        }
        if (!is_array($error)) {
            return;
        }
        if (error_reporting() === 0) {
            return;
        }
        // not fatal
        if (!in_array($error['type'], [E_ERROR, E_COMPILE_ERROR, E_CORE_ERROR])) {
            return;
        }

        echo "\n\n\nFATAL ERROR. TESTS NOT FINISHED.\n";
        echo sprintf("%s \nin %s:%d\n", $error['message'], $error['file'], $error['line']);
    }

    private function registerDeprecationErrorHandler()
    {
        if (class_exists('\Symfony\Bridge\PhpUnit\DeprecationErrorHandler') && 'disabled' !== getenv('SYMFONY_DEPRECATIONS_HELPER')) {
            // DeprecationErrorHandler only will be installed if array('PHPUnit\Util\ErrorHandler', 'handleError')
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
        if (!($this->errorLevel & $type)) {
            return;
        }
        if (strpos($message, 'Symfony 4.3')) { // skip Symfony 4.3 deprecations
            return;
        }
        if ($this->deprecationsInstalled && $this->oldHandler) {
            call_user_func($this->oldHandler, $type, $message, $file, $line, $context);
            return;
        }
        Notification::deprecate("$message", "$file:$line");
    }
}
