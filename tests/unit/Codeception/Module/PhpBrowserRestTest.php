<?php

use Codeception\Util\Stub as Stub;

class PhpBrowserRestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\Module\REST
     */
    protected $module;

    /**
     * @var \Codeception\Module\PhpBrowser
     */
    protected $phpBrowser;

    public function setUp() {
        $this->phpBrowser = new \Codeception\Module\PhpBrowser(make_container());
        $url = 'http://localhost:8010';
        $this->phpBrowser->_setConfig(array('url' => $url));
        $this->phpBrowser->_initialize();

        $this->module = Stub::make('\Codeception\Module\REST');
        $this->module->_inject($this->phpBrowser);
        $this->module->_initialize();
        $this->module->_before(Stub::makeEmpty('\Codeception\TestCase\Cest'));
        $this->phpBrowser->_before(Stub::makeEmpty('\Codeception\TestCase\Cest'));

    }

    public function testGet() {
        $this->module->sendGET('/rest/user/');
        $this->module->seeResponseIsJson();
        $this->module->seeResponseContains('davert');
        $this->module->seeResponseContainsJson(array('name' => 'davert'));
        $this->module->seeResponseCodeIs(200);
        $this->module->dontSeeResponseCodeIs(404);
    }

    public function testPost() {
        $this->module->sendPOST('/rest/user/', array('name' => 'john'));
        $this->module->seeResponseContains('john');
        $this->module->seeResponseContainsJson(array('name' => 'john'));
    }

    public function testGrabDataFromJsonResponse() {
        $this->module->sendGET('/rest/user/');
        // simple assoc array
        $this->assertEquals('davert@mail.ua', $this->module->grabDataFromJsonResponse('email'));
        // nested assoc array
        $this->assertEquals('Kyiv', $this->module->grabDataFromJsonResponse('address.city'));
        // nested index array
        $this->assertEquals('DavertMik', $this->module->grabDataFromJsonResponse('aliases.0'));
        // fail if data not found
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError', 'Response does not have required data');
        $this->module->grabDataFromJsonResponse('address.street');
    }

    public function testValidJson()
    {
        $this->module->response = '{"xxx": "yyy"}';
        $this->module->seeResponseIsJson();
        $this->module->response = '{"xxx": "yyy", "zzz": ["a","b"]}';
        $this->module->seeResponseIsJson();
        $this->module->seeResponseEquals($this->module->response);
    }

    public function testInvalidJson()
    {
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->module->response = '{xxx = yyy}';
        $this->module->seeResponseIsJson();
    }
    public function testValidXml()
    {
        $this->module->response = '<xml></xml>';
        $this->module->seeResponseIsXml();
        $this->module->response = '<xml><name>John</name></xml>';
        $this->module->seeResponseIsXml();
        $this->module->seeResponseEquals($this->module->response);
    }

    public function testInvalidXml()
    {
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->module->response = '<xml><name>John</surname></xml>';
        $this->module->seeResponseIsXml();
    }

    public function testSeeInJson()
    {
        $this->module->response = '{"ticket": {"title": "Bug should be fixed", "user": {"name": "Davert"}, "labels": null}}';
        $this->module->seeResponseIsJson();
        $this->module->seeResponseContainsJson(array('name' => 'Davert'));
        $this->module->seeResponseContainsJson(array('user' => array('name' => 'Davert')));
        $this->module->seeResponseContainsJson(array('ticket' => array('title' => 'Bug should be fixed')));
        $this->module->seeResponseContainsJson(array('ticket' => array('user' => array('name' => 'Davert'))));
        $this->module->seeResponseContainsJson(array('ticket' => array('labels' => null)));
    }

    public function testSeeInJsonCollection()
    {
        $this->module->response = '[{"user":"Blacknoir","age":27,"tags":["wed-dev","php"]},{"user":"John Doe","age":27,"tags":["web-dev","java"]}]';
        $this->module->seeResponseIsJson();
        $this->module->seeResponseContainsJson(array('tags' => array('web-dev', 'java')));
        $this->module->seeResponseContainsJson(array('user' => 'John Doe', 'age' => 27));
    }


    public function testArrayJson()
    {
        $this->module->response = '[{"id":1,"title": "Bug should be fixed"},{"title": "Feature should be implemented","id":2}]';
        $this->module->seeResponseContainsJson(array('id' => 1));
    }

    public function testDontSeeInJson()
    {
        $this->module->response = '{"ticket": {"title": "Bug should be fixed", "user": {"name": "Davert"}}}';
        $this->module->seeResponseIsJson();
        $this->module->dontSeeResponseContainsJson(array('name' => 'Davet'));
        $this->module->dontSeeResponseContainsJson(array('user' => array('name' => 'Davet')));
        $this->module->dontSeeResponseContainsJson(array('user' => array('title' => 'Bug should be fixed')));
    }

    public function testApplicationJsonIncludesJsonAsContent()
    {
        $this->module->haveHttpHeader('Content-Type', 'application/json');
        $this->module->sendPOST('/', array('name' => 'john'));
        /** @var $request \Symfony\Component\BrowserKit\Request  **/
        $request = $this->module->client->getRequest();
        $this->assertContains('application/json', $request->getServer());
        $server = $request->getServer();
        $this->assertEquals('application/json', $server['HTTP_CONTENT_TYPE']);
        $this->assertJson($request->getContent());
        $this->assertEmpty($request->getParameters());
    }

    public function testGetApplicationJsonNotIncludesJsonAsContent()
    {
        $this->module->haveHttpHeader('Content-Type', 'application/json');
        $this->module->sendGET('/', array('name' => 'john'));
        /** @var $request \Symfony\Component\BrowserKit\Request  **/
        $request = $this->module->client->getRequest();
        $this->assertNull($request->getContent());
        $this->assertContains('john', $request->getParameters());
    }

    public function testUrlIsFull()
    {
        $this->module->sendGET('/api/v1/users');
        /** @var $request \Symfony\Component\BrowserKit\Request  **/
        $request = $this->module->client->getRequest();
        $this->assertEquals('http://localhost:8010/api/v1/users',$request->getUri());
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/issues/1650
     */
    public function testHostHeaders()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $this->markTestSkipped('only for php 5.5');
        }
        $this->module->haveHttpHeader('Host','http://www.example.com');
        $this->module->sendGET('/rest/ping/');
        $this->module->seeResponseContains('host: http://www.example.com');
    }


    protected function shouldFail()
    {
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError');
    }

}
