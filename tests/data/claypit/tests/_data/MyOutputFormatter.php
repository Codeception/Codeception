<?php

class MyOutputFormatter extends \Codeception\Platform\Extension {

    public function _reconfigure()
    {
        // we silenced default formatter
        $this->options['silent'] = false;
    }

    // we are listening for events
    static $events = array(
        'suite.before' => 'beforeSuite',
        'test.end' => 'after',
        'test.success' => 'success',
        'test.fail' => 'fail',
        'test.error' => 'error',
    );

    public function beforeSuite()
    {
        $this->writeln("");
    }

    public function success()
    {
        $this->write('[+] ');
    }

    public function fail()
    {
        $this->write('[-] ');
    }

    public function error()
    {
        $this->write('[E] ');
    }

    // we are printing test status and time taken
    public function after(\Codeception\Event\Test $e)
    {
        $seconds_input = $e->getTime();
        // stack overflow: http://stackoverflow.com/questions/16825240/how-to-convert-microtime-to-hhmmssuu
        $seconds = (int)($milliseconds = (int)($seconds_input * 1000)) / 1000;
        $time = ($seconds%60).(($milliseconds===0)?'':'.'.$milliseconds);

        $this->write($e->getTest()->getFeature());
        $this->writeln(' ('.$time .'s)');
    }
}
