<?php

use Codeception\Util\Stub as Stub;

class RestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\Module\REST
     */
    protected $module;

    public function setUp() {
        $this->module = new \Codeception\Module\REST();
        $connector = new \Codeception\Util\Connector\Universal();
        $connector->setIndex(\Codeception\Configuration::dataDir().'/rest/index.php');
        $this->module->client = $connector;
        $this->module->_before(Stub::makeEmpty('\Codeception\TestCase\Cest'));
        $this->module->client->setServerParameters(array(
            'SCRIPT_FILENAME' => 'index.php',
            'SCRIPT_NAME' => 'index',
            'SERVER_NAME' => 'localhost',
            'SERVER_PROTOCOL' => 'http'
        ));
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

    public function testPut() {
        $this->module->sendPUT('/rest/user/', array('name' => 'laura'));
        $this->module->seeResponseContains('davert@mail.ua');
        $this->module->seeResponseContainsJson(array('name' => 'laura'));
        $this->module->dontSeeResponseContainsJson(array('name' => 'john'));
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

    public function testSeeInJson()
    {
        $this->module->response = '{"ticket": {"title": "Bug should be fixed", "user": {"name": "Davert"}, "labels": null}}';
        $this->module->seeResponseIsJson();
        $this->module->seeResponseContainsJson(array('ticket' => array('title' => 'Bug should be fixed')));
        $this->module->seeResponseContainsJson(array('ticket' => array('user' => array('name' => 'Davert'))));
        $this->module->seeResponseContainsJson(array('ticket' => array('labels' => null)));
    }

    public function testDontSeeInJson()
    {
        $this->module->response = '{"ticket": {"title": "Bug should be fixed", "user": {"name": "Davert"}}}';
        $this->module->seeResponseIsJson();
        $this->module->dontSeeResponseContainsJson(array('user' => array('name' => 'Davert')));
        $this->module->dontSeeResponseContainsJson(array('title' => 'Bug should be fixed'));
    }

    public function testApplicationJsonIncludesJsonAsContent()
    {
        $this->module->haveHttpHeader('Content-Type', 'application/json');
        $this->module->sendPOST('/', array('name' => 'john'));
        /** @var $request \Symfony\Component\BrowserKit\Request  **/
        $request = $this->module->client->getRequest();
        $this->assertContains('application/json', $request->getServer());
        $server = $request->getServer();
        $this->assertEquals('application/json', $server['HTTP_Content-Type']);
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
        $this->assertEquals('http://localhost/api/v1/users',$request->getUri());
    }
}
