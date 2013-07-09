<?php

class MyOutputFormatter extends \Codeception\Platform\Extension {

    public function _reconfigure()
    {
        $this->options['silent'] = false;
    }

    static $events = array(
        'test.end' => 'after',
        'test.success' => 'success',
        'test.fail' => 'fail',
        'test.error' => 'error',
    );

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

    public function after(\Codeception\Event\Test $e)
    {
        $seconds_input = $e->getTime();
        // stack overflow: http://stackoverflow.com/questions/16825240/how-to-convert-microtime-to-hhmmssuu
        $seconds = (int)($milliseconds = (int)($seconds_input * 1000)) / 1000;
        $time = ($seconds%60).(($milliseconds===0)?'':'.'.$milliseconds);

        $this->write($e->getTest()->getFeature());
        $this->writeln(' | '.$time .'s');
    }
}
