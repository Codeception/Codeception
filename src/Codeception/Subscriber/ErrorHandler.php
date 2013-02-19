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
    private static $fatalErrors;
    public function handle() {
        /**
         * There are some other nasty constants and PHP likes to chanhge their meanings with updates.
         * @see http://www.php.net/manual/ru/errorfunc.constants.php
         */
        self::$fatalErrors = E_ALL & ~(E_NOTICE | E_WARNING | E_STRICT | E_CORE_WARNING | E_COMPILE_WARNING | E_USER_WARNING | E_DEPRECATED);
        error_reporting(E_ERROR | E_PARSE);
        set_error_handler(array(__CLASS__, 'errorHandler'), self::$fatalErrors);
        register_shutdown_function(array(__CLASS__, 'shutdownHandler'));
    }

    public static function errorHandler($errno, $errstr, $errfile, $errline) {
        if (strpos($errstr, 'Cannot modify header information')!==false)
            return false;
        if ($errno > 8) throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public static function shutdownHandler() {
        if (self::$stopped)
            return;
        self::$stopped = true;
        $error = error_get_last();
        if (!is_array($error)) return;

        // Non-fatal warnings occured in process shouldn't make codecept rant after completion.
        if (!($error['type'] & self::$fatalErrors))
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
