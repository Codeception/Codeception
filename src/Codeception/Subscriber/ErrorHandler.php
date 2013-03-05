<?php
namespace Codeception\Subscriber;

use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ErrorHandler implements EventSubscriberInterface
{
    /**
     * @var bool $stopped to keep shutdownHandler from possible looping.
     */
    private static $stopped = false;

    /**
     * @var int stores bitmask for fatal errors
     */
    private static $errorLevel;
    public function handle() {
        $config = \Codeception\Configuration::config();
        self::$errorLevel = eval("return {$config['settings']['error_level']};");

        error_reporting(self::$errorLevel);
        set_error_handler(array(__CLASS__, 'errorHandler'), self::$errorLevel);
        register_shutdown_function(array(__CLASS__, 'shutdownHandler'));
    }

    public static function errorHandler($errno, $errstr, $errfile, $errline) {
        if (error_reporting() === 0) return false;
        if (strpos($errstr, 'Cannot modify header information')!==false)
            return false;
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public static function shutdownHandler() {
        if (self::$stopped)
            return;
        self::$stopped = true;
        $error = error_get_last();
        if (!is_array($error)) return;

        // Non-fatal warnings occurred in process shouldn't make codecept rant after completion.
        if (!($error['type'] & self::$errorLevel))
            return;

        echo "\n\n\nFATAL ERROR OCCURRED. TESTS NOT FINISHED.\n";
        echo sprintf("%s \nin %s:%d\n", $error['message'], $error['file'], $error['line']);
    }

    static function getSubscribedEvents()
    {
        return array(
            'suite.before' => 'handle'
        );
    }
}
