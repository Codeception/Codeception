<?php
namespace Codeception\Subscriber;

use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ErrorHandler implements EventSubscriberInterface
{
    public function handle() {
        error_reporting(E_ERROR | E_PARSE);

        set_error_handler(function ($errno, $errstr, $errfile, $errline ) {
            if (strpos($errstr, 'Cannot modify header information')!==false) return false;
            if ($errno > 8) throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
        );
        register_shutdown_function(function () {

             $error = error_get_last();
             if (!is_array($error)) return;
             echo "\n\n\nFATAL ERROR OCCURRED. TESTS NOT FINISHED.\n";
             echo sprintf("%s \nin %s:%d\n", $error['message'], $error['file'], $error['line']);
         });
    }

    static function getSubscribedEvents()
    {
        return array(
            'suite.before' => 'handle'
        );
    }
}
