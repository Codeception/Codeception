<?php

use Codeception\Util\Stub;
require_once 'tests/data/app/data.php';
require_once __DIR__ . '/TestsForBrowsers.php';

class PhpBrowserTest extends TestsForBrowsers
{
    /**
     * @var \Codeception\Module\PhpBrowser
     */
    protected $module;

    /**
     * @var \GuzzleHttp\Subscriber\History
     */
    protected $history;

    protected function setUp() {
        $this->module = new \Codeception\Module\PhpBrowser(make_container());
        $url = 'http://localhost:8000';
        $this->module->_setConfig(array('url' => $url));
        $this->module->_initialize();
        $this->module->_cleanup();
        $this->module->_before($this->makeTest());
        $this->history = new \GuzzleHttp\Subscriber\History();
        $this->module->guzzle->getEmitter()->attach($this->history);
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

    public function testSetCookieByHeader()
    {
        $this->module->amOnPage('/cookies2');
        $this->module->seeResponseCodeIs(200);
        $this->module->seeCookie('a');
        $this->assertEquals('b', $this->module->grabCookie('a'));
    }

    public function testUrlSlashesFormatting()
    {
        $this->module->amOnPage('somepage.php');
        $this->module->seeCurrentUrlEquals('/somepage.php');
        $this->module->amOnPage('///somepage.php');
        $this->module->seeCurrentUrlEquals('/somepage.php');
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

    public function testChangeDomains()
    {
        $this->mockResponse();
        $this->module->amOnSubdomain('user');
        $this->module->amOnPage('/form1');
        $this->assertEquals('http://user.localhost:8000/form1', $this->module->client->getHistory()->current()->getUri());
    }

    public function testHeadersByConfig()
    {
        $this->mockResponse();
        $this->module->_setConfig(['headers' => ['xxx' => 'yyyy']]);
        $this->module->_initialize();
        $this->module->amOnPage('/form1');
        $this->assertArrayHasKey('xxx', $this->module->guzzle->getDefaultOption('headers'));
        $this->assertEquals('yyyy', $this->module->guzzle->getDefaultOption('headers/xxx'));
    }

    public function testHeadersBySetHeader()
    {
        $this->module->setHeader('xxx', 'yyyy');
        $this->module->amOnPage('/');
        $this->assertTrue($this->history->getLastRequest()->hasHeader('xxx'));
    }

    public function testCurlOptions()
    {
        $this->module->_setConfig(array('url' => 'http://google.com', 'curl' => array('CURLOPT_NOBODY' => true)));
        $this->module->_initialize();
        $this->assertTrue($this->module->guzzle->getDefaultOption('config/curl/'.CURLOPT_NOBODY));

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

    protected function mockResponse($body = "hello", $code = 200)
    {
        $mock = new \GuzzleHttp\Subscriber\Mock([
            new \GuzzleHttp\Message\Response($code, [], \GuzzleHttp\Stream\Stream::factory($body))
        ]);
        $this->module->guzzle->getEmitter()->attach($mock);
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
        $this->setExpectedException("\\Codeception\\Exception\\TestRuntime");
        $this->module->fillField('#name', 'Nothing special');
    }
}
