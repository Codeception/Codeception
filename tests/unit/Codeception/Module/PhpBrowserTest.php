<?php

use Codeception\Util\Stub;
require_once 'tests/data/app/data.php';
require_once __DIR__.'/TestsForMink.php';

class PhpBrowserTest extends TestsForMink
{
    /**
     * @var \Codeception\Module\PhpBrowser
     */
    protected $module;

    // this is my local config
    protected $is_local = false;

    public function setUp() {
        $this->noPhpWebserver();
        $this->module = new \Codeception\Module\PhpBrowser();
        $url = '';
        if (version_compare(PHP_VERSION, '5.4', '>=')) $url = 'http://localhost:8000';
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
        return Stub::makeEmpty('\Codeception\TestCase\Cept', array('dispatcher' => Stub::makeEmpty('Symfony\Component\EventDispatcher\EventDispatcher')));
    }

    protected function noPhpWebserver()
    {
        if (version_compare(PHP_VERSION, '5.4', '<') and (! $this->is_local)) {
            $this->markTestSkipped('Requires PHP built-in web server, available only in PHP 5.4.');
        }
    }

    public function testSubmitForm() {
        $this->module->amOnPage('/form/complex');
        $this->module->submitForm('form', array('name' => 'Davert'));
        $form = data::get('form');
        $this->assertEquals('Davert', $form['name']);
        $this->assertEquals('kill_all', $form['action']);

    }

    public function testAjax() {
        $this->module->amOnPage('/');
        $this->module->sendAjaxGetRequest('/info');
        $this->assertNotNull(data::get('ajax'));

        $this->module->sendAjaxPostRequest('/form/complex', array('show' => 'author'));
        $this->assertNotNull(data::get('ajax'));
        $post = data::get('form');
        $this->assertEquals('author', $post['show']);
    }

    public function testLinksWithNonLatin() {
        $this->module->amOnPage('/info');
        $this->module->seeLink('Ссылочка');
        $this->module->click('Ссылочка');
    }

}
