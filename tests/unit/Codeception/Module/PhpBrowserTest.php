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

    protected function setUp() {
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

    protected function tearDown() {
        $this->noPhpWebserver();
        if ($this->module) {
            $this->module->_after($this->makeTest());
        }
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

    public function testCurlOptions()
    {
        $guzzle = $this->module->guzzle;
        $opts = $guzzle->getConfig('curl.options');
        $this->assertFalse($opts[CURLOPT_SSL_VERIFYPEER]);
        $this->assertFalse($opts[CURLOPT_CERTINFO]);

        $module = new \Codeception\Module\PhpBrowser();
        //
        $module->_setConfig(array('url' => 'http://google.com', 'curl' => array('CURLOPT_NOBODY' => true)));
        $module->_initialize();
        $guzzle = $module->guzzle;
        $opts = $guzzle->getConfig('curl.options');
        $this->assertTrue($opts[CURLOPT_NOBODY]);

    }

    public function testSubmitForm()
    {
        $this->module->amOnPage('/form/complex');
        $this->module->submitForm('form', array(
                'name' => 'Davert',
                'description' => 'Is Codeception maintainer'
        ));
        $form = data::get('form');
        $this->assertEquals('Davert', $form['name']);
        $this->assertEquals('Is Codeception maintainer', $form['description']);
        $this->assertFalse(isset($form['disabled_fieldset']));
        $this->assertFalse(isset($form['disabled_field']));
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
    
	public function testSetMultipleCookies() {
        $cookie_name_1  = 'test_cookie';
        $cookie_value_1 = 'this is a test';
        $this->module->setCookie($cookie_name_1, $cookie_value_1);

        $cookie_name_2  = '2_test_cookie';
        $cookie_value_2 = '2 this is a test';
        $this->module->setCookie($cookie_name_2, $cookie_value_2);

        $this->module->seeCookie($cookie_name_1);
        $this->module->seeCookie($cookie_name_2);
        $this->module->dontSeeCookie('evil_cookie');

        $cookie1 = $this->module->grabCookie($cookie_name_1);
        $this->assertEquals($cookie_value_1, $cookie1);

        $cookie2 = $this->module->grabCookie($cookie_name_2);
        $this->assertEquals($cookie_value_2, $cookie2);

        $this->module->resetCookie($cookie_name_1);
        $this->module->dontSeeCookie($cookie_name_1);
        $this->module->seeCookie($cookie_name_2);
        $this->module->resetCookie($cookie_name_2);
        $this->module->dontSeeCookie($cookie_name_2);
    }

    public function testMultipleCookies() {
        $this->module->amOnPage('/');
        $this->module->sendAjaxPostRequest('/cookies');
        $this->module->seeCookie('foo', 'bar1');
        $this->module->seeCookie('baz', 'bar2');
        $this->module->setCookie('foo', 'bar1');
        $this->module->setCookie('baz', 'bar2');
        $this->module->amOnPage('/cookies');
        $this->module->seeInCurrentUrl('info');
    }      
    
    public function testSubmitFormGet()
    {
        $I = $this->module;
        $I->amOnPage('/search');
        $I->submitForm('form', array('searchQuery' => 'test'));
        $I->see('Success');
    }
}
