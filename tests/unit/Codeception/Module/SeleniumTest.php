<?php

use Codeception\Util\Stub;
require_once 'tests/data/app/data.php';
require_once __DIR__.'/TestsForMink.php';

class SeleniumTest extends TestsForMink
{
    /**
     * @var \Codeception\Module\Selenium
     */
    protected $module;

    // this is my local config
    protected $is_local = false;

    public function setUp() {
        $this->noPhpWebserver();
        $this->noSelenium();
        $this->module = new \Codeception\Module\PhpBrowser();
        $url = '';
        if (strpos(PHP_VERSION, '5.4')===0) $url = 'http://localhost:8000';
        // my local config.
        if ($this->is_local) $url = 'http://testapp.com';

        $this->module->_setConfig(array('url' => $url));
        $this->module->_initialize();
        $this->module->_cleanup();
        $this->module->_before($this->makeTest());
    }
    
    public function tearDown() {
        $this->noPhpWebserver();
        $this->module->_after($this->makeTest());
        data::clean();
    }

    protected function makeTest()
    {
        return Stub::makeEmpty('\Codeception\TestCase', array('dispatcher' => Stub::makeEmpty('Symfony\Component\EventDispatcher\EventDispatcher')));
    }

    protected function noSelenium()
    {
        $fp = @fsockopen('localhost', 4444);
        if ($fp !== false) {
            fclose($fp);
            return true;
        }
        $this->markTestSkipped(
            'Requires Selenium Server running'
        );
        return false;
    }


    protected function noPhpWebserver() {
        if ((strpos(PHP_VERSION, '5.4')!==0) and (!$this->is_local))
        $this->markTestSkipped(
          'Requires PHP built-in web server, available only in PHP 5.4.'
        );
    }
    
}