<?php

use Codeception\Util\Stub;
require_once 'tests/data/app/data.php';
require_once __DIR__ . '/TestsForBrowsers.php';
use GuzzleHttp\Psr7\Response;

class PhpBrowserTest extends TestsForBrowsers
{
    /**
     * @var \Codeception\Module\PhpBrowser
     */
    protected $module;

    protected $history = [];

    protected function setUp() {
        $this->module = new \Codeception\Module\PhpBrowser(make_container());
        $url = 'http://localhost:8000';
        $this->module->_setConfig(array('url' => $url));
        $this->module->_initialize();
        $this->module->_cleanup();
        $this->module->_before($this->makeTest());
        if (class_exists('GuzzleHttp\Url')) {
            $this->history = new \GuzzleHttp\Subscriber\History();
            $this->module->guzzle->getEmitter()->attach($this->history);
        } else {
            $this->module->guzzle->getConfig('handler')->push(\GuzzleHttp\Middleware::history($this->history));
        }

    }

    private function getLastRequest()
    {
        if (is_array($this->history)) {
            return end($this->history)['request'];
        } else {
            return $this->history->getLastRequest();
        }
    }
    
    protected function tearDown() {
        if ($this->module) {
            $this->module->_after($this->makeTest());
        }
        data::clean();
    }

    protected function makeTest()
    {
        return Stub::makeEmpty('\Codeception\TestCase\Cept', array('dispatcher' => Stub::makeEmpty('Symfony\Component\EventDispatcher\EventDispatcher')));
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
        $this->module->amOnPage('/');
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

    public function testSubmitFormGet()
    {
        $I = $this->module;
        $I->amOnPage('/search');
        $I->submitForm('form', array('searchQuery' => 'test'));
        $I->see('Success');
    }

    public function testHtmlRedirect()
    {
        $this->module->amOnPage('/redirect2');
        $this->module->seeResponseCodeIs(200);
        $this->module->seeCurrentUrlEquals('/info');
        
        $this->module->amOnPage('/redirect_interval');
        $this->module->seeCurrentUrlEquals('/redirect_interval');
    }

    public function testHtmlRedirectWithParams()
    {
        $this->module->amOnPage('/redirect_params');
        $this->module->seeResponseCodeIs(200);
        $this->module->seeCurrentUrlEquals('/search?one=1&two=2'); 
    }
    
    public function testMetaRefresh()
    {
        $this->module->amOnPage('/redirect_self');
        $this->module->see('Redirecting to myself');
    }
    
    public function testRefreshRedirect()
    {
        $this->module->amOnPage('/redirect3');
        $this->module->seeResponseCodeIs(200);
        $this->module->seeCurrentUrlEquals('/info');
        
        $this->module->amOnPage('/redirect_header_interval');
        $this->module->seeCurrentUrlEquals('/redirect_header_interval');
        $this->module->see('Welcome to test app!');
    }

    public function testRedirectWithGetParams()
    {
        $this->module->amOnPage('/redirect4');
        $this->module->seeInCurrentUrl('/search?ln=test@gmail.com&sn=testnumber');
        $params = data::get('params');
        $this->assertContains('test@gmail.com', $params);
    }

    public function testRedirectBaseUriHasPath()
    {
        // prepare config
        $config = $this->module->_getConfig();
        $config['url'] .= '/somepath'; // append path to the base url
        $this->module->_reconfigure($config);

        $this->module->amOnPage('/redirect_base_uri_has_path');
        $this->module->seeResponseCodeIs(200);
        $this->module->seeCurrentUrlEquals('/somepath/info');
        $this->module->see('Lots of valuable data here');
    }

    public function testRedirectBaseUriHasPathAnd302Code()
    {
        // prepare config
        $config = $this->module->_getConfig();
        $config['url'] .= '/somepath'; // append path to the base url
        $this->module->_reconfigure($config);

        $this->module->amOnPage('/redirect_base_uri_has_path_302');
        $this->module->seeResponseCodeIs(200);
        $this->module->seeCurrentUrlEquals('/somepath/info');
        $this->module->see('Lots of valuable data here');
    }

    public function testRelativeRedirect()
    {
        // test relative redirects where the effective request URI is in a
        // subdirectory
        $this->module->amOnPage('/relative/redirect');
        $this->module->seeResponseCodeIs(200);
        $this->module->seeCurrentUrlEquals('/relative/info');

        // also, test relative redirects where the effective request URI is not
        // in a subdirectory
        $this->module->amOnPage('/relative_redirect');
        $this->module->seeResponseCodeIs(200);
        $this->module->seeCurrentUrlEquals('/info');
    }

    public function testChainedRedirects()
    {
        $this->module->amOnPage('/redirect_twice');
        $this->module->seeResponseCodeIs(200);
        $this->module->seeCurrentUrlEquals('/info');
    }

    public function testDisabledRedirects()
    {
        $this->module->client->followRedirects(false);
        $this->module->amOnPage('/redirect_twice');
        $this->module->seeResponseCodeIs(302);
        $this->module->seeCurrentUrlEquals('/redirect_twice');
    }

    public function testRedirectLimitReached()
    {
        $this->module->client->setMaxRedirects(1);
        try {
            $this->module->amOnPage('/redirect_twice');
            $this->assertTrue(false, 'redirect limit is not respected');
        } catch (\LogicException $e) {
            $this->assertEquals('The maximum number (1) of redirections was reached.', $e->getMessage(), 'redirect limit is respected');
        }
    }

    public function testRedirectLimitNotReached()
    {
        $this->module->client->setMaxRedirects(2);
        $this->module->amOnPage('/redirect_twice');
        $this->module->seeResponseCodeIs(200);
        $this->module->seeCurrentUrlEquals('/info');
    }

    public function testSetCookieByHeader()
    {
        $this->module->amOnPage('/cookies2');
        $this->module->seeResponseCodeIs(200);
        $this->module->seeCookie('a');
        $this->assertEquals('b', $this->module->grabCookie('a'));
        $this->module->seeCookie('c');
    }

    public function testUrlSlashesFormatting()
    {
        $this->module->amOnPage('somepage.php');
        $this->module->seeCurrentUrlEquals('/somepage.php');
        $this->module->amOnPage('///somepage.php');
        $this->module->seeCurrentUrlEquals('/somepage.php');
    }

    public function testSettingContentTypeFromHtml()
    {
        $this->module->amOnPage('/content-iso');
        $charset = $this->module->client->getResponse()->getHeader('Content-Type');
        $this->assertEquals('text/html;charset=ISO-8859-1', $charset);
    }

    public function testSettingCharsetFromHtml()
    {
        $this->module->amOnPage('/content-cp1251');
        $charset = $this->module->client->getResponse()->getHeader('Content-Type');
        $this->assertEquals('text/html;charset=windows-1251', $charset);
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/issues/933
     */
    public function testSubmitFormWithQueries()
    {
        $this->module->amOnPage('/form/example3');
        $this->module->seeElement('form');
        $this->module->submitForm('form', array(
                'name' => 'jon',
        ));
        $form = data::get('form');
        $this->assertEquals('jon', $form['name']);
        $this->module->seeCurrentUrlEquals('/form/example3?validate=yes');
    }

    public function testHeadersByConfig()
    {
        $this->module->_setConfig(['headers' => ['xxx' => 'yyyy']]);
        $this->module->_initialize();
        $this->module->amOnPage('/form1');

        if (method_exists($this->module->guzzle, 'getConfig')) {
            $headers = $this->module->guzzle->getConfig('headers');
        } else {
            $headers = $this->module->guzzle->getDefaultOption('headers');
        }
        $this->assertArrayHasKey('xxx', $headers);
    }

    public function testHeadersBySetHeader()
    {

        $this->module->setHeader('xxx', 'yyyy');
        $this->module->amOnPage('/');
        $this->assertTrue($this->getLastRequest()->hasHeader('xxx'));
    }

    public function testDeleteHeaders()
    {
        $this->module->setHeader('xxx', 'yyyy');
        $this->module->deleteHeader('xxx');
        $this->module->amOnPage('/');
        $this->assertFalse($this->getLastRequest()->hasHeader('xxx'));
    }

    public function testDeleteHeadersByEmptyValue()
    {
        $this->module->setHeader('xxx', 'yyyy');
        $this->module->setHeader('xxx', '');
        $this->module->amOnPage('/');
        $this->assertFalse($this->getLastRequest()->hasHeader('xxx'));
    }

    public function testCurlOptions()
    {
        $this->module->_setConfig(array('url' => 'http://google.com', 'curl' => array('CURLOPT_NOBODY' => true)));
        $this->module->_initialize();
        if (method_exists($this->module->guzzle, 'getConfig')) {
            $config = $this->module->guzzle->getConfig('config');
        } else {
            $config = $this->module->guzzle->getDefaultOption('config');
        }
        $this->assertArrayHasKey('curl', $config);
        $this->assertArrayHasKey('CURLOPT_NOBODY', $config['curl']);
    }

    public function testHttpAuth()
    {
        $this->module->amOnPage('/auth');
        $this->module->seeResponseCodeIs(401);
        $this->module->see('Unauthorized');
        $this->module->amHttpAuthenticated('davert', 'password');
        $this->module->amOnPage('/auth');
        $this->module->dontSee('Unauthorized');
        $this->module->see("Welcome, davert");
        $this->module->amHttpAuthenticated('davert', '123456');
        $this->module->amOnPage('/auth');
        $this->module->see('Forbidden');
    }

    public function testRawGuzzle()
    {
        $code = $this->module->executeInGuzzle(function(\GuzzleHttp\Client $client) {
            $res = $client->get('/info');
            return $res->getStatusCode();
        });
        $this->assertEquals(200, $code);
    }

    /**
     * If we have a form with fields like
     * ```
     * <input type="file" name="foo" />
     * <input type="file" name="foo[bar]" />
     * ```
     * then only array variable will be used while simple variable will be ignored in php $_FILES
     * (eg $_FILES = [
     *                 foo => [
     *                     tmp_name => [
     *                         'bar' => 'asdf'
     *                     ],
     *                     //...
     *                ]
     *              ]
     * )
     * (notice there is no entry for file "foo", only for file "foo[bar]"
     * this will check if current element contains inner arrays within it's keys
     * so we can ignore element itself and only process inner files
     */
    public function testFormWithFilesInOnlyArray()
    {
        $this->shouldFail();
        $this->module->amOnPage('/form/example13');
        $this->module->attachFile('foo', 'app/avatar.jpg');
        $this->module->attachFile('foo[bar]', 'app/avatar.jpg');
        $this->module->click('Submit');
    }
    
    public function testDoubleSlash()
    {
        $I = $this->module;
        $I->amOnPage('/register');
        $I->submitForm('form', array('test' => 'test'));
        $formUrl = $this->module->client->getHistory()->current()->getUri();
        $formPath = parse_url($formUrl)['path'];
        $this->assertEquals($formPath, '/register');
    }

    public function testFillFieldWithoutPage()
    {
        $this->setExpectedException("\\Codeception\\Exception\\ModuleException");
        $this->module->fillField('#name', 'Nothing special');
    }
    
    public function testArrayFieldSubmitForm()
    {
        $this->skipForOldGuzzle();

        $this->module->amOnPage('/form/example17');
        $this->module->submitForm(
            'form',
            [
                'FooBar' => ['bar' => 'booze'],
                'Food' => [
                    'beer' => [
                        'yum' => ['yeah' => 'crunked']
                    ]
                ]
            ]
        );
        $data = data::get('form');
        $this->assertEquals('booze', $data['FooBar']['bar']);
        $this->assertEquals('crunked', $data['Food']['beer']['yum']['yeah']);
    }

    public function testCookiesForDomain()
    {
        $this->skipForOldGuzzle();

        $mock = new \GuzzleHttp\Handler\MockHandler([
            new Response(200, ['X-Foo' => 'Bar']),
        ]);
        $handler = \GuzzleHttp\HandlerStack::create($mock);
        $handler->push(\GuzzleHttp\Middleware::history($this->history));
        $client = new \GuzzleHttp\Client(['handler' => $handler, 'base_uri' => 'http://codeception.com']);
        $guzzleConnector = new \Codeception\Lib\Connector\Guzzle6();
        $guzzleConnector->setClient($client);
        $guzzleConnector->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie('hello', 'world'));
        $guzzleConnector->request('GET', 'http://codeception.com/');
        $this->assertArrayHasKey('cookies', $this->history[0]['options']);
        /** @var $cookie GuzzleHttp\Cookie\SetCookie  **/
        $cookies = $this->history[0]['options']['cookies']->toArray();
        $cookie = reset($cookies);
        $this->assertEquals('codeception.com', $cookie['Domain']);
    }

    private function skipForOldGuzzle()
    {
        if (class_exists('GuzzleHttp\Url')) {
            $this->markTestSkipped("Not for Guzzle <6");
        }
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/2234
     */
    public function testEmptyValueOfCookie()
    {
      //set cookie
      $this->module->amOnPage('/cookies2');

      $this->module->amOnPage('/unset-cookie');
      $this->module->seeResponseCodeIs(200);
      $this->module->dontSeeCookie('a');
    }

    public function testRequestApi()
    {
        $this->setExpectedException('Codeception\Exception\ModuleException');
        $response = $this->module->_request('POST', '/form/try', ['user' => 'davert']);
        $data = data::get('form');
        $this->assertEquals('davert', $data['user']);
        $this->assertInternalType('string', $response);
        $this->assertContains('Welcome to test app', $response);
        $this->module->click('Welcome to test app'); // page not loaded
    }

    public function testLoadPageApi()
    {
        $this->module->_loadPage('POST', '/form/try', ['user' => 'davert']);
        $data = data::get('form');
        $this->assertEquals('davert', $data['user']);
        $this->module->see('Welcome to test app');
        $this->module->click('More info');
        $this->module->seeInCurrentUrl('/info');
    }
}
