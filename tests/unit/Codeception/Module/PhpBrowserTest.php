<?php

use Codeception\Exception\TestRuntimeException;
use Codeception\Util\Stub;

require_once 'tests/data/app/data.php';
require_once __DIR__ . '/TestsForBrowsers.php';

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;

class PhpBrowserTest extends TestsForBrowsers
{
    /**
     * @var \Codeception\Module\PhpBrowser
     */
    protected $module;

    protected $history = [];

    protected function _setUp()
    {
        $this->module = new \Codeception\Module\PhpBrowser(make_container());
        $url = 'http://localhost:8000';
        $this->module->_setConfig(['url' => $url]);
        $this->module->_initialize();
        $this->module->_before($this->makeTest());
        $this->module->guzzle->getConfig('handler')->push(\GuzzleHttp\Middleware::history($this->history));

    }

    private function getLastRequest()
    {
        if (is_array($this->history)) {
            return end($this->history)['request'];
        }

        return $this->history->getLastRequest();
    }

    protected function _tearDown()
    {
        if ($this->module) {
            $this->module->_after($this->makeTest());
        }
        data::clean();
    }

    protected function makeTest()
    {
        return Stub::makeEmpty('\Codeception\Test\Cept');
    }

    public function testAjax()
    {
        $this->module->amOnPage('/');
        $this->module->sendAjaxGetRequest('/info');
        $this->assertNotNull(data::get('ajax'));

        $this->module->sendAjaxPostRequest('/form/complex', array('show' => 'author'));
        $this->assertNotNull(data::get('ajax'));
        $post = data::get('form');
        $this->assertEquals('author', $post['show']);
    }

    public function testLinksWithNonLatin()
    {
        $this->module->amOnPage('/info');
        $this->module->seeLink('Ссылочка');
        $this->module->click('Ссылочка');
    }

    public function testHtmlSnapshot()
    {
        $this->module->amOnPage('/');
        $testName="debugPhpBrowser";
        $this->module->makeHtmlSnapshot($testName);
        $this->assertFileExists(\Codeception\Configuration::outputDir().'debug/'.$testName.'.html');
        @unlink(\Codeception\Configuration::outputDir().'debug/'.$testName.'.html');
    }

    /**
     * @see https://github.com/Codeception/Codeception/issues/4509
     */
    public function testSeeTextAfterJSComparisionOperator()
    {
        $this->module->amOnPage('/info');
        $this->module->see('Text behind JS comparision');
    }

    public function testSetMultipleCookies()
    {
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

    public function testSessionsHaveIndependentCookies()
    {
        $this->module->amOnPage('/');
        $cookie_name_1  = 'test_cookie';
        $cookie_value_1 = 'this is a test';
        $this->module->setCookie($cookie_name_1, $cookie_value_1);

        $session = $this->module->_backupSession();
        $this->module->_initializeSession();

        $this->module->dontSeeCookie($cookie_name_1);

        $cookie_name_2  = '2_test_cookie';
        $cookie_value_2 = '2 this is a test';
        $this->module->setCookie($cookie_name_2, $cookie_value_2);

        $this->module->_loadSession($session);

        $this->module->dontSeeCookie($cookie_name_2);
        $this->module->seeCookie($cookie_name_1);
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
        $this->module->amOnPage('/redirect_meta_refresh');
        $this->module->seeResponseCodeIs(200);
        $this->module->seeCurrentUrlEquals('/info');
    }

    public function testMetaRefreshIsIgnoredIfIntervalIsLongerThanMaxInterval()
    {
        // prepare config
        $config = $this->module->_getConfig();
        $config['refresh_max_interval'] = 3; // less than 9
        $this->module->_reconfigure($config);
        $this->module->amOnPage('/redirect_meta_refresh');
        $this->module->seeResponseCodeIs(200);
        $this->module->seeCurrentUrlEquals('/redirect_meta_refresh');
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
            $this->assertEquals(
                'The maximum number (1) of redirections was reached.',
                $e->getMessage(),
                'redirect limit is respected'
            );
        }
    }

    public function testRedirectLimitNotReached()
    {
        $this->module->client->setMaxRedirects(2);
        $this->module->amOnPage('/redirect_twice');
        $this->module->seeResponseCodeIs(200);
        $this->module->seeCurrentUrlEquals('/info');
    }

    public function testLocationHeaderDoesNotRedirectWhenStatusCodeIs201()
    {
        $this->module->amOnPage('/location_201');
        $this->module->seeResponseCodeIs(201);
        $this->module->seeCurrentUrlEquals('/location_201');
    }

    public function testRedirectToAnotherDomainUsingSchemalessUrl()
    {

        $this->module->_reconfigure([
            'handler' => new MockHandler([
                new Response(302, ['Location' => '//example.org/']),
                new Response(200, [], 'Cool stuff')
            ])
        ]);
        /** @var \GuzzleHttp\HandlerStack $handlerStack */
        $this->module->amOnUrl('http://fictional.redirector/redirect-to?url=//example.org/');
        $currentUrl = $this->module->client->getHistory()->current()->getUri();
        $this->assertSame('http://example.org/', $currentUrl);
    }

    public function testSetCookieByHeader()
    {
        $this->module->amOnPage('/cookies2');
        $this->module->seeResponseCodeIs(200);
        $this->module->seeCookie('a');
        $this->assertEquals('b', $this->module->grabCookie('a'));
        $this->module->seeCookie('c');
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
            $config = $this->module->guzzle->getConfig();
        } else {
            $config = $this->module->guzzle->getDefaultOption('config');
        }
        $this->assertArrayHasKey('curl', $config);
        $this->assertArrayHasKey(CURLOPT_NOBODY, $config['curl']);
    }


    public function testCurlSslOptions()
    {
        $this->module->_setConfig(array(
            'url' => 'https://google.com',
            'curl' => array(
                'CURLOPT_NOBODY' => true,
                'CURLOPT_SSL_CIPHER_LIST' => 'TLSv1',
            )));
        $this->module->_initialize();
        $config = $this->module->guzzle->getConfig();

        $this->assertArrayHasKey('curl', $config);
        $this->assertArrayHasKey(CURLOPT_SSL_CIPHER_LIST, $config['curl']);
        $this->module->amOnPage('/');
        $this->assertSame('', $this->module->_getResponseContent(), 'CURLOPT_NOBODY setting is not respected');
    }

    public function testHttpAuth()
    {
        $this->module->amOnPage('/auth');
        $this->module->seeResponseCodeIs(401);
        $this->module->see('Unauthorized');
        $this->module->amHttpAuthenticated('davert', 'password');
        $this->module->amOnPage('/auth');
        $this->module->seeResponseCodeIs(200);
        $this->module->dontSee('Unauthorized');
        $this->module->see("Welcome, davert");
        $this->module->amHttpAuthenticated(null, null);
        $this->module->amOnPage('/auth');
        $this->module->seeResponseCodeIs(401);
        $this->module->amHttpAuthenticated('davert', '123456');
        $this->module->amOnPage('/auth');
        $this->module->see('Forbidden');
    }

    public function testRawGuzzle()
    {
        $code = $this->module->executeInGuzzle(function (\GuzzleHttp\Client $client) {
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
        $this->expectException("\\Codeception\\Exception\\ModuleException");
        $this->module->fillField('#name', 'Nothing special');
    }

    public function testArrayFieldSubmitForm()
    {
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
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar']),
        ]);
        $handler = \GuzzleHttp\HandlerStack::create($mock);
        $handler->push(\GuzzleHttp\Middleware::history($this->history));
        $client = new \GuzzleHttp\Client(['handler' => $handler, 'base_uri' => 'http://codeception.com']);
        $guzzleConnector = new \Codeception\Lib\Connector\Guzzle();
        $guzzleConnector->setClient($client);
        $guzzleConnector->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie('hello', 'world'));
        $guzzleConnector->request('GET', 'http://codeception.com/');
        $this->assertArrayHasKey('cookies', $this->history[0]['options']);
        /** @var $cookie GuzzleHttp\Cookie\SetCookie  **/
        $cookies = $this->history[0]['options']['cookies']->toArray();
        $cookie = reset($cookies);
        $this->assertEquals('codeception.com', $cookie['Domain']);
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/2653
     */
    public function testSetCookiesByOptions()
    {
        $config = $this->module->_getConfig();
        $config['cookies'] = [
            [
                'Name' => 'foo',
                'Value' => 'bar1',
            ],
            [
                'Name' => 'baz',
                'Value' => 'bar2',
            ],
        ];
        $this->module->_reconfigure($config);
        // this url redirects if cookies are present
        $this->module->amOnPage('/cookies');
        $this->module->seeCurrentUrlEquals('/info');
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
        $this->expectException('Codeception\Exception\ModuleException');
        $response = $this->module->_request('POST', '/form/try', ['user' => 'davert']);
        $data = data::get('form');
        $this->assertEquals('davert', $data['user']);
        $this->assertIsString($response);
        $this->assertStringContainsString('Welcome to test app', $response);
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

    /**
     * @issue https://github.com/Codeception/Codeception/issues/2408
     */
    public function testClickFailure()
    {
        $this->module->amOnPage('/info');
        $this->expectException('Codeception\Exception\ElementNotFound');
        $this->expectExceptionMessage("'Sign In!' is invalid CSS and XPath selector and Link or Button element with 'name=Sign In!' was not found");
        $this->module->click('Sign In!');
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/2841
     */
    public function testSubmitFormDoesNotKeepGetParameters()
    {
        $this->module->amOnPage('/form/bug2841?stuff=other');
        $this->module->fillField('#texty', 'thingshjere');
        $this->module->click('#submit-registration');
        $this->assertEmpty(data::get('query'), 'Query string is not empty');
    }

    public function testClickLinkAndFillField()
    {
        $this->module->amOnPage('/info');
        $this->module->click('Sign in!');
        $this->module->seeCurrentUrlEquals('/login');
        $this->module->fillField('email', 'email@example.org');
    }

    public function testClickSelectsClickableElementFromMatches()
    {
        $this->module->amOnPage('/form/multiple_matches');
        $this->module->click('Press Me!');
        $this->module->seeCurrentUrlEquals('/info');
    }

    public function testClickSelectsClickableElementFromMatchesUsingCssLocator()
    {
        $this->module->amOnPage('/form/multiple_matches');
        $this->module->click(['css' => '.link']);
        $this->module->seeCurrentUrlEquals('/info');
    }

    public function testClickingOnButtonOutsideFormDoesNotCauseFatalError()
    {
        $this->expectException(TestRuntimeException::class);
        $this->expectExceptionMessage('Button is not inside a link or a form');
        $this->module->amOnPage('/form/button-not-in-form');
        $this->module->click(['xpath' => '//input[@type="submit"][@form="form-id"]']);
    }

    public function testSubmitFormWithoutEmptyOptionsInSelect()
    {
        $this->module->amOnPage('/form/bug3824');
        $this->module->submitForm('form', []);
        $this->module->dontSee('ERROR');
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/3953
     */
    public function testFillFieldInGetFormWithoutId()
    {
        $this->module->amOnPage('/form/bug3953');
        $this->module->selectOption('select_name', 'two');
        $this->module->fillField('search_name', 'searchterm');
        $this->module->click('Submit');
        $params = data::get('query');
        $this->assertEquals('two', $params['select_name']);
        $this->assertEquals('searchterm', $params['search_name']);
    }

    public function testGrabPageSourceWhenNotOnPage()
    {
        $this->expectException('\Codeception\Exception\ModuleException');
        $this->expectExceptionMessage('Page not loaded. Use `$I->amOnPage` (or hidden API methods `_request` and `_loadPage`) to open it');
        $this->module->grabPageSource();
    }

    public function testGrabPageSourceWhenOnPage()
    {
        $this->module->amOnPage('/minimal');
        $sourceExpected =
<<<HTML
<!DOCTYPE html>
<html>
    <head>
        <title>
            Minimal page
        </title>
    </head>
    <body>
        <h1>
            Minimal page
        </h1>
    </body>
</html>

HTML
        ;
        $sourceActual = $this->module->grabPageSource();
        $this->assertXmlStringEqualsXmlString($sourceExpected, $sourceActual);
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/4383
     */
    public function testSecondAmOnUrlWithEmptyPath()
    {
        $this->module->amOnUrl('http://localhost:8000/info');
        $this->module->see('Lots of valuable data here');
        $this->module->amOnUrl('http://localhost:8000');
        $this->module->dontSee('Lots of valuable data here');
    }

    public function testSetUserAgentUsingConfig()
    {
        $this->module->_setConfig(['headers' => ['User-Agent' => 'Codeception User Agent Test 1.0']]);
        $this->module->_initialize();

        $this->module->amOnPage('/user-agent');
        $response = $this->module->grabPageSource();
        $this->assertEquals('Codeception User Agent Test 1.0', $response, 'Incorrect user agent');
    }

    public function testIfStatusCodeIsWithin2xxRange()
    {
        $this->module->amOnPage('https://httpstat.us/200');
        $this->module->seeResponseCodeIsSuccessful();

        $this->module->amOnPage('https://httpstat.us/299');
        $this->module->seeResponseCodeIsSuccessful();
    }

    public function testIfStatusCodeIsWithin3xxRange()
    {
        $this->module->amOnPage('https://httpstat.us/300');
        $this->module->seeResponseCodeIsRedirection();

        $this->module->amOnPage('https://httpstat.us/399');
        $this->module->seeResponseCodeIsRedirection();
    }

    public function testIfStatusCodeIsWithin4xxRange()
    {
        $this->module->amOnPage('https://httpstat.us/400');
        $this->module->seeResponseCodeIsClientError();

        $this->module->amOnPage('https://httpstat.us/499');
        $this->module->seeResponseCodeIsClientError();
    }

    public function testIfStatusCodeIsWithin5xxRange()
    {
        $this->module->amOnPage('https://httpstat.us/500');
        $this->module->seeResponseCodeIsServerError();

        $this->module->amOnPage('https://httpstat.us/599');
        $this->module->seeResponseCodeIsServerError();
    }
}
