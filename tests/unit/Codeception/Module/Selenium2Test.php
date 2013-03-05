<?php

use Codeception\Util\Stub;

require_once 'tests/data/app/data.php';
require_once __DIR__ . '/TestsForMink.php';

class Selenium2Test extends TestsForMink
{
    /**
     * @var \Codeception\Module\Selenium
     */
    protected $module;

    // this is my local config
    protected $is_local = false;

    public function setUp()
    {
        $this->noPhpWebserver();
        $this->noSelenium();
        $this->module = new \Codeception\Module\Selenium2();
        $url = '';
        if (strpos(PHP_VERSION, '5.4') === 0) {
            $url = 'http://localhost:8000';
        }
        // my local config.
        if ($this->is_local) {
            $url = 'http://testapp.com';
        }

        $this->module->_setConfig(array('url' => $url, 'browser' => 'firefox', 'port' => '4455'));
        $this->module->_initialize();
        $this->module->_cleanup();
        $this->module->_before($this->makeTest());
    }

    public function tearDown()
    {
        $this->noPhpWebserver();
        $this->noSelenium();
        $this->module->_after($this->makeTest());
        data::clean();
    }

    protected function makeTest()
    {
        return Stub::makeEmpty(
            '\Codeception\TestCase\Cept',
            array('dispatcher' => Stub::makeEmpty('Symfony\Component\EventDispatcher\EventDispatcher'))
        );
    }

    protected function noSelenium()
    {
        $fp = @fsockopen('localhost', 4455);
        if ($fp !== false) {
            fclose($fp);
            return true;
        }
        $this->markTestSkipped(
            'Requires Selenium2 Server running on port 4455'
        );
        return false;
    }


    protected function noPhpWebserver()
    {
        if ((strpos(PHP_VERSION, '5.4') !== 0) and (!$this->is_local)) {
            $this->markTestSkipped(
                'Requires PHP built-in web server, available only in PHP 5.4.'
            );
        }
    }

    public function testSelectByLabel()
    {
        // In Selenium you can't select option by it's value
    }

}