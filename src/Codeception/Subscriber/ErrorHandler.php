<?php
namespace Codeception\Subscriber;

use Codeception\Event\Suite;
use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ErrorHandler implements EventSubscriberInterface
{
    /**
     * @var bool $stopped to keep shutdownHandler from possible looping.
     */
    private static $stopped = false;

    /**
     * @var int stores bitmask for errors
     */
    private $errorLevel = 'E_ALL';

    public function handle(Suite $e) {

        $settings = $e->getSettings();
        if ($settings['error_level']) {
            $this->errorLevel = eval("return {$settings['error_level']};");
        }
        error_reporting($this->errorLevel);
        set_error_handler(array(__CLASS__, 'errorHandler'));
        register_shutdown_function(array(__CLASS__, 'shutdownHandler'));
    }

    public static function errorHandler($errno, $errstr, $errfile, $errline) {

        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting
            return false;
        }

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
        echo error_reporting();
        print_r($error);
        if (error_reporting() === 0) return;

        echo "\n\n\nFATAL ERROR. TESTS NOT FINISHED.\n";
        echo sprintf("%s \nin %s:%d\n", $error['message'], $error['file'], $error['line']);
    }

    static function getSubscribedEvents()
    {
        return array(
            'suite.before' => 'handle'
        );
    }
}
