<?php

use Codeception\Util\Stub as Stub;

/**
 * Class RestTest
 * @group appveyor
 */
class RestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\Module\REST
     */
    protected $module;

    public function setUp()
    {
        $connector = new \Codeception\Lib\Connector\Universal();
        $connector->setIndex(\Codeception\Configuration::dataDir() . '/rest/index.php');

        $connectionModule = new \Codeception\Module\UniversalFramework(make_container());
        $connectionModule->client = $connector;
        $connectionModule->_initialize();
        $this->module = Stub::make('\Codeception\Module\REST');
        $this->module->_inject($connectionModule);
        $this->module->_initialize();
        $this->module->_before(Stub::makeEmpty('\Codeception\TestCase\Cest'));
        $this->module->client->setServerParameters([
            'SCRIPT_FILENAME' => 'index.php',
            'SCRIPT_NAME' => 'index',
            'SERVER_NAME' => 'localhost',
            'SERVER_PROTOCOL' => 'http'
        ]);
    }

    public function testBeforeHookResetsVariables()
    {
        $this->module->haveHttpHeader('Origin','http://www.example.com');
        $this->module->sendGET('/rest/user/');
        $this->assertEquals(
            'http://www.example.com',
            $this->module->client->getServerParameter('HTTP_ORIGIN')
        );

        $this->module->_before(Stub::makeEmpty('\Codeception\TestCase\Cest'));
        $this->assertNull($this->module->client->getServerParameter('HTTP_ORIGIN', null));
    }

    public function testGet()
    {
        $this->module->sendGET('/rest/user/');
        $this->module->seeResponseIsJson();
        $this->module->seeResponseContains('davert');
        $this->module->seeResponseContainsJson(['name' => 'davert']);
        $this->module->seeResponseCodeIs(200);
        $this->module->dontSeeResponseCodeIs(404);
    }

    public function testPost()
    {
        $this->module->sendPOST('/rest/user/', ['name' => 'john']);
        $this->module->seeResponseContains('john');
        $this->module->seeResponseContainsJson(['name' => 'john']);
    }

    public function testPut()
    {
        $this->module->sendPUT('/rest/user/', ['name' => 'laura']);
        $this->module->seeResponseContains('davert@mail.ua');
        $this->module->seeResponseContainsJson(['name' => 'laura']);
        $this->module->dontSeeResponseContainsJson(['name' => 'john']);
    }

    public function testGrabDataFromResponseByJsonPath()
    {
        $this->module->sendGET('/rest/user/');
        // simple assoc array
        $this->assertEquals(['davert@mail.ua'], $this->module->grabDataFromResponseByJsonPath('$.email'));
        // nested assoc array
        $this->assertEquals(['Kyiv'], $this->module->grabDataFromResponseByJsonPath('$.address.city'));
        // nested index array
        $this->assertEquals(['DavertMik'], $this->module->grabDataFromResponseByJsonPath('$.aliases[0]'));
        // empty if data not found
        $this->assertEquals([], $this->module->grabDataFromResponseByJsonPath('$.address.street'));
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

    public function testSeeInJsonResponse()
    {
        $this->module->response = '{"ticket": {"title": "Bug should be fixed", "user": {"name": "Davert"}, "labels": null}}';
        $this->module->seeResponseIsJson();
        $this->module->seeResponseContainsJson(['name' => 'Davert']);
        $this->module->seeResponseContainsJson(['user' => ['name' => 'Davert']]);
        $this->module->seeResponseContainsJson(['ticket' => ['title' => 'Bug should be fixed']]);
        $this->module->seeResponseContainsJson(['ticket' => ['user' => ['name' => 'Davert']]]);
        $this->module->seeResponseContainsJson(['ticket' => ['labels' => null]]);
    }

    public function testSeeInJsonCollection()
    {
        $this->module->response = '[{"user":"Blacknoir","age":"42","tags":["wed-dev","php"]},{"user":"John Doe","age":27,"tags":["web-dev","java"]}]';
        $this->module->seeResponseIsJson();
        $this->module->seeResponseContainsJson(['tags' => ['web-dev', 'java']]);
        $this->module->seeResponseContainsJson(['user' => 'John Doe', 'age' => 27]);
        $this->module->seeResponseContainsJson([['user' => 'John Doe', 'age' => 27]]);
        $this->module->seeResponseContainsJson([['user' => 'Blacknoir', 'age' => 42], ['user' => 'John Doe', 'age' => "27"]]);
    }

    public function testArrayJson()
    {
        $this->module->response = '[{"id":1,"title": "Bug should be fixed"},{"title": "Feature should be implemented","id":2}]';
        $this->module->seeResponseContainsJson(['id' => 1]);
    }

    public function testDontSeeInJson()
    {
        $this->module->response = '{"ticket": {"title": "Bug should be fixed", "user": {"name": "Davert"}}}';
        $this->module->seeResponseIsJson();
        $this->module->dontSeeResponseContainsJson(['name' => 'Davet']);
        $this->module->dontSeeResponseContainsJson(['user' => ['name' => 'Davet']]);
        $this->module->dontSeeResponseContainsJson(['user' => ['title' => 'Bug should be fixed']]);
    }

    public function testApplicationJsonIncludesJsonAsContent()
    {
        $this->module->haveHttpHeader('Content-Type', 'application/json');
        $this->module->sendPOST('/', ['name' => 'john']);
        /** @var $request \Symfony\Component\BrowserKit\Request  **/
        $request = $this->module->client->getRequest();
        $this->assertContains('application/json', $request->getServer());
        $server = $request->getServer();
        $this->assertEquals('application/json', $server['HTTP_CONTENT_TYPE']);
        $this->assertJson($request->getContent());
        $this->assertEmpty($request->getParameters());
    }

    public function testApplicationJsonIncludesObjectSerialized()
    {
        $this->module->haveHttpHeader('Content-Type', 'application/json');
        $this->module->sendPOST('/', new JsonSerializedItem());
        /** @var $request \Symfony\Component\BrowserKit\Request  **/
        $request = $this->module->client->getRequest();
        $this->assertContains('application/json', $request->getServer());
        $this->assertJson($request->getContent());
    }

    public function testGetApplicationJsonNotIncludesJsonAsContent()
    {
        $this->module->haveHttpHeader('Content-Type', 'application/json');
        $this->module->sendGET('/', ['name' => 'john']);
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

    public function testSeeHeaders()
    {
        $response = new \Symfony\Component\BrowserKit\Response("", 200, [
            'Cache-Control' => ['no-cache', 'no-store'],
            'Content_Language' => 'en-US'
        ]);
        $this->module->client->mockResponse($response);
        $this->module->sendGET('/');
        $this->module->seeHttpHeader('Cache-Control');
        $this->module->seeHttpHeader('content_language','en-US');
        $this->module->seeHttpHeader('Content-Language','en-US');
        $this->module->dontSeeHttpHeader('Content-Language','en-RU');
        $this->module->dontSeeHttpHeader('Content-Language1');
        $this->module->seeHttpHeaderOnce('Content-Language');
        $this->assertEquals('en-US', $this->module->grabHttpHeader('Content-Language'));
        $this->assertEquals('no-cache', $this->module->grabHttpHeader('Cache-Control'));
        $this->assertEquals(['no-cache', 'no-store'], $this->module->grabHttpHeader('Cache-Control', false));

    }

    public function testSeeHeadersOnce()
    {
        $this->shouldFail();
        $response = new \Symfony\Component\BrowserKit\Response("", 200, [
            'Cache-Control' => ['no-cache', 'no-store'],
        ]);
        $this->module->client->mockResponse($response);
        $this->module->sendGET('/');
        $this->module->seeHttpHeaderOnce('Cache-Control');
    }

    public function testArrayJsonPathAndXPath()
    {
        $this->module->response = '[{"user":"Blacknoir","age":27,"tags":["wed-dev","php"]},{"user":"John Doe","age":27,"tags":["web-dev","java"]}]';
        $this->module->seeResponseIsJson();
        $this->module->seeResponseJsonMatchesXpath('//user');
        $this->module->seeResponseJsonMatchesJsonPath('$[*].user');
        $this->module->seeResponseJsonMatchesJsonPath('$[1].tags');
        $this->module->dontSeeResponseJsonMatchesJsonPath('$[*].profile');
    }

    
    public function testArrayJsonPathFails()
    {
        $this->shouldFail();
        $this->module->response = '[{"user":"Blacknoir","age":27,"tags":["wed-dev","php"]},{"user":"John Doe","age":27,"tags":["web-dev","java"]}]';
        $this->module->seeResponseIsJson();
        $this->module->seeResponseJsonMatchesJsonPath('$[*].profile');
    }
    
    
    public function testStructuredJsonPathAndXPath()
    {
        $this->module->response = '{ "store": {"book": [{ "category": "reference", "author": "Nigel Rees", "title": "Sayings of the Century", "price": 8.95 }, { "category": "fiction", "author": "Evelyn Waugh", "title": "Sword of Honour", "price": 12.99 }, { "category": "fiction", "author": "Herman Melville", "title": "Moby Dick", "isbn": "0-553-21311-3", "price": 8.99 }, { "category": "fiction", "author": "J. R. R. Tolkien", "title": "The Lord of the Rings", "isbn": "0-395-19395-8", "price": 22.99 } ], "bicycle": {"color": "red", "price": 19.95 } } }';
        $this->module->seeResponseIsJson();
        $this->module->seeResponseJsonMatchesXpath('//book/category');
        $this->module->seeResponseJsonMatchesJsonPath('$..book');
        $this->module->seeResponseJsonMatchesJsonPath('$.store.book[2].author');
        $this->module->dontSeeResponseJsonMatchesJsonPath('$.invalid');
        $this->module->dontSeeResponseJsonMatchesJsonPath('$.store.book.*.invalidField');
    }

    public function testApplicationJsonSubtypeIncludesObjectSerialized()
    {
        $this->module->haveHttpHeader('Content-Type', 'application/resource+json');
        $this->module->sendPOST('/', new JsonSerializedItem());
        /** @var $request \Symfony\Component\BrowserKit\Request  **/
        $request = $this->module->client->getRequest();
        $this->assertContains('application/resource+json', $request->getServer());
        $this->assertJson($request->getContent());
    }

    public function testJsonTypeMatches()
    {
        $this->module->response = '{"xxx": "yyy", "user_id": 1}';
        $this->module->seeResponseMatchesJsonType(['xxx' => 'string', 'user_id' => 'integer:<10']);
        $this->module->dontSeeResponseMatchesJsonType(['xxx' => 'integer', 'user_id' => 'integer:<10']);
    }

    public function testJsonTypeMatchesWithJsonPath()
    {
        $this->module->response = '{"users": [{ "name": "davert"}, {"id": 1}]}';
        $this->module->seeResponseMatchesJsonType(['name' => 'string'], '$.users[0]');
        $this->module->seeResponseMatchesJsonType(['id' => 'integer'], '$.users[1]');
        $this->module->dontSeeResponseMatchesJsonType(['id' => 'integer'], '$.users[0]');
    }


    protected function shouldFail()
    {
        $this->setExpectedException('PHPUnit_Framework_AssertionFailedError');
    }

}

class JsonSerializedItem implements JsonSerializable
{
    public function jsonSerialize()
    {
        return array("hello" => "world");
    }
}
