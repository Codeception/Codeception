<?php
namespace Codeception\Subscriber;

use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ErrorHandler implements EventSubscriberInterface
{
    public function handle() {
        error_reporting(E_ERROR | E_PARSE);

        set_error_handler(function ($errno, $errstr, $errfile, $errline ) {
            if (strpos($errstr, 'Cannot modify header information')!==false) return false;
            if (error_reporting()) throw new \ErrorException($errno . " ".$errstr, 0, $errno, $errfile, $errline); }
        );
        register_shutdown_function(function () {

             $error = error_get_last();
             if ($error['type'] == 1) {
                 echo 'FATAL ERROR OCCURRED. TESTS NOT FINISHED.';
             }
         });
    }

    static function getSubscribedEvents()
    {
        return array(
            'test.before' => 'handle'
        );
    }
}
