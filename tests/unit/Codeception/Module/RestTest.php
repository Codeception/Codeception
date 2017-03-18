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
        $this->module->_before(Stub::makeEmpty('\Codeception\Test\Test'));
        $this->module->client->setServerParameters([
            'SCRIPT_FILENAME' => 'index.php',
            'SCRIPT_NAME' => 'index',
            'SERVER_NAME' => 'localhost',
            'SERVER_PROTOCOL' => 'http'
        ]);
    }

    public function testConflictsWithAPI()
    {
        $this->assertInstanceOf('Codeception\Lib\Interfaces\ConflictsWithModule', $this->module);
        $this->assertEquals('Codeception\Lib\Interfaces\API', $this->module->_conflicts());
    }

    private function setStubResponse($response)
    {
        $connectionModule = Stub::make('\Codeception\Module\UniversalFramework', ['_getResponseContent' => $response]);
        $this->module->_inject($connectionModule);
        $this->module->_initialize();
        $this->module->_before(Stub::makeEmpty('\Codeception\Test\Test'));
    }

    public function testBeforeHookResetsVariables()
    {
        $this->module->haveHttpHeader('Origin', 'http://www.example.com');
        $this->module->sendGET('/rest/user/');
        $server = $this->module->client->getInternalRequest()->getServer();
        $this->assertArrayHasKey('HTTP_ORIGIN', $server);
        $this->module->_before(Stub::makeEmpty('\Codeception\Test\Test'));
        $this->module->sendGET('/rest/user/');
        $server = $this->module->client->getInternalRequest()->getServer();
        $this->assertArrayNotHasKey('HTTP_ORIGIN', $server);
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
        $this->setStubResponse('{"xxx": "yyy"}');
        $this->module->seeResponseIsJson();
        $this->setStubResponse('{"xxx": "yyy", "zzz": ["a","b"]}');
        $this->module->seeResponseIsJson();
        $this->module->seeResponseEquals('{"xxx": "yyy", "zzz": ["a","b"]}');
    }

    public function testInvalidJson()
    {
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->setStubResponse('{xxx = yyy}');
        $this->module->seeResponseIsJson();
    }
    public function testValidXml()
    {
        $this->setStubResponse('<xml></xml>');
        $this->module->seeResponseIsXml();
        $this->setStubResponse('<xml><name>John</name></xml>');
        $this->module->seeResponseIsXml();
        $this->module->seeResponseEquals('<xml><name>John</name></xml>');
    }

    public function testInvalidXml()
    {
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->setStubResponse('<xml><name>John</surname></xml>');
        $this->module->seeResponseIsXml();
    }

    public function testSeeInJsonResponse()
    {
        $this->setStubResponse(
            '{"ticket": {"title": "Bug should be fixed", "user": {"name": "Davert"}, "labels": null}}'
        );
        $this->module->seeResponseIsJson();
        $this->module->seeResponseContainsJson(['name' => 'Davert']);
        $this->module->seeResponseContainsJson(['user' => ['name' => 'Davert']]);
        $this->module->seeResponseContainsJson(['ticket' => ['title' => 'Bug should be fixed']]);
        $this->module->seeResponseContainsJson(['ticket' => ['user' => ['name' => 'Davert']]]);
        $this->module->seeResponseContainsJson(['ticket' => ['labels' => null]]);
    }

    public function testSeeInJsonCollection()
    {
        $this->setStubResponse(
            '[{"user":"Blacknoir","age":"42","tags":["wed-dev","php"]},'
            . '{"user":"John Doe","age":27,"tags":["web-dev","java"]}]'
        );
        $this->module->seeResponseIsJson();
        $this->module->seeResponseContainsJson(['tags' => ['web-dev', 'java']]);
        $this->module->seeResponseContainsJson(['user' => 'John Doe', 'age' => 27]);
        $this->module->seeResponseContainsJson([['user' => 'John Doe', 'age' => 27]]);
        $this->module->seeResponseContainsJson(
            [['user' => 'Blacknoir', 'age' => 42], ['user' => 'John Doe', 'age' => "27"]]
        );
    }

    public function testArrayJson()
    {
        $this->setStubResponse(
            '[{"id":1,"title": "Bug should be fixed"},{"title": "Feature should be implemented","id":2}]'
        );
        $this->module->seeResponseContainsJson(['id' => 1]);
    }

    public function testDontSeeInJson()
    {
        $this->setStubResponse('{"ticket": {"title": "Bug should be fixed", "user": {"name": "Davert"}}}');
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
        $this->assertEquals('http://localhost/api/v1/users', $request->getUri());
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
        $this->module->seeHttpHeader('content_language', 'en-US');
        $this->module->seeHttpHeader('Content-Language', 'en-US');
        $this->module->dontSeeHttpHeader('Content-Language', 'en-RU');
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

    public function testSeeResponseJsonMatchesXpath()
    {
        $this->setStubResponse(
            '[{"user":"Blacknoir","age":27,"tags":["wed-dev","php"]},'
            . '{"user":"John Doe","age":27,"tags":["web-dev","java"]}]'
        );
        $this->module->seeResponseIsJson();
        $this->module->seeResponseJsonMatchesXpath('//user');
    }

    public function testSeeResponseJsonMatchesJsonPath()
    {
        $this->setStubResponse(
            '[{"user":"Blacknoir","age":27,"tags":["wed-dev","php"]},'
            . '{"user":"John Doe","age":27,"tags":["web-dev","java"]}]'
        );
        $this->module->seeResponseJsonMatchesJsonPath('$[*].user');
        $this->module->seeResponseJsonMatchesJsonPath('$[1].tags');
    }


    public function testDontSeeResponseJsonMatchesJsonPath()
    {
        $this->setStubResponse(
            '[{"user":"Blacknoir","age":27,"tags":["wed-dev","php"]},'
            . '{"user":"John Doe","age":27,"tags":["web-dev","java"]}]'
        );
        $this->module->dontSeeResponseJsonMatchesJsonPath('$[*].profile');
    }

    public function testDontSeeResponseJsonMatchesXpath()
    {
        $this->setStubResponse(
            '[{"user":"Blacknoir","age":27,"tags":["wed-dev","php"]},'
            . '{"user":"John Doe","age":27,"tags":["web-dev","java"]}]'
        );
        $this->module->dontSeeResponseJsonMatchesXpath('//status');
    }

    public function testDontSeeResponseJsonMatchesXpathFails()
    {
        $this->shouldFail();
        $this->setStubResponse(
            '[{"user":"Blacknoir","age":27,"tags":["wed-dev","php"]},'
            . '{"user":"John Doe","age":27,"tags":["web-dev","java"]}]'
        );
        $this->module->dontSeeResponseJsonMatchesXpath('//user');
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/issues/2775
     */
    public function testSeeResponseJsonMatchesXPathWorksWithAmpersand()
    {
        $this->setStubResponse('{ "product":[ { "category":[ { "comment":"something & something" } ] } ] }');
        $this->module->seeResponseIsJson();
        $this->module->seeResponseJsonMatchesXpath('//comment');
    }

    
    public function testSeeResponseJsonMatchesJsonPathFails()
    {
        $this->shouldFail();
        $this->setStubResponse(
            '[{"user":"Blacknoir","age":27,"tags":["wed-dev","php"]},'
            . '{"user":"John Doe","age":27,"tags":["web-dev","java"]}]'
        );
        $this->module->seeResponseIsJson();
        $this->module->seeResponseJsonMatchesJsonPath('$[*].profile');
    }
    
    
    public function testStructuredJsonPathAndXPath()
    {
        $this->setStubResponse(
            '{ "store": {"book": [{ "category": "reference", "author": "Nigel Rees", '
            . '"title": "Sayings of the Century", "price": 8.95 }, { "category": "fiction", "author": "Evelyn Waugh", '
            . '"title": "Sword of Honour", "price": 12.99 }, { "category": "fiction", "author": "Herman Melville", '
            . '"title": "Moby Dick", "isbn": "0-553-21311-3", "price": 8.99 }, { "category": "fiction", '
            . '"author": "J. R. R. Tolkien", "title": "The Lord of the Rings", "isbn": "0-395-19395-8", '
            . '"price": 22.99 } ], "bicycle": {"color": "red", "price": 19.95 } } }'
        );
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
        $this->setStubResponse('{"xxx": "yyy", "user_id": 1}');
        $this->module->seeResponseMatchesJsonType(['xxx' => 'string', 'user_id' => 'integer:<10']);
        $this->module->dontSeeResponseMatchesJsonType(['xxx' => 'integer', 'user_id' => 'integer:<10']);
    }

    public function testJsonTypeMatchesWithJsonPath()
    {
        $this->setStubResponse('{"users": [{ "name": "davert"}, {"id": 1}]}');
        $this->module->seeResponseMatchesJsonType(['name' => 'string'], '$.users[0]');
        $this->module->seeResponseMatchesJsonType(['id' => 'integer'], '$.users[1]');
        $this->module->dontSeeResponseMatchesJsonType(['id' => 'integer'], '$.users[0]');
    }

    public function testMatchJsonTypeFailsWithNiceMessage()
    {
        $this->setStubResponse('{"xxx": "yyy", "user_id": 1}');
        try {
            $this->module->seeResponseMatchesJsonType(['zzz' => 'string']);
            $this->fail('it had to throw exception');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            $this->assertEquals('Key `zzz` doesn\'t exist in {"xxx":"yyy","user_id":1}', $e->getMessage());
        }
    }

    public function testDontMatchJsonTypeFailsWithNiceMessage()
    {
        $this->setStubResponse('{"xxx": "yyy", "user_id": 1}');
        try {
            $this->module->dontSeeResponseMatchesJsonType(['xxx' => 'string']);
            $this->fail('it had to throw exception');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            $this->assertEquals('Unexpectedly response matched: {"xxx":"yyy","user_id":1}', $e->getMessage());
        }
    }

    public function testSeeResponseIsJsonFailsWhenResponseIsEmpty()
    {
        $this->shouldFail();
        $this->setStubResponse('');
        $this->module->seeResponseIsJson();
    }

    public function testSeeResponseIsJsonFailsWhenResponseIsInvalidJson()
    {
        $this->shouldFail();
        $this->setStubResponse('{');
        $this->module->seeResponseIsJson();
    }

    public function testSeeResponseJsonMatchesXpathCanHandleResponseWithOneElement()
    {
        $this->setStubResponse('{"success": 1}');
        $this->module->seeResponseJsonMatchesXpath('//success');
    }

    public function testSeeResponseJsonMatchesXpathCanHandleResponseWithTwoElements()
    {
        $this->setStubResponse('{"success": 1, "info": "test"}');
        $this->module->seeResponseJsonMatchesXpath('//success');
    }

    public function testSeeResponseJsonMatchesXpathCanHandleResponseWithOneSubArray()
    {
        $this->setStubResponse('{"array": {"success": 1}}');
        $this->module->seeResponseJsonMatchesXpath('//array/success');
    }

    public function testSeeBinaryResponseEquals()
    {
        $data = base64_decode('/9j/2wBDAAMCAgICAgMCAgIDAwMDBAYEBAQEBAgGBgUGCQgKCgkICQkKDA8MCgsOCwkJDRENDg8QEBEQCgwSExIQEw8QEBD/yQALCAABAAEBAREA/8wABgAQEAX/2gAIAQEAAD8A0s8g/9k=');
        $this->setStubResponse($data);
        $this->module->seeBinaryResponseEquals(md5($data));
    }

    public function testDontSeeBinaryResponseEquals()
    {
        $data = base64_decode('/9j/2wBDAAMCAgICAgMCAgIDAwMDBAYEBAQEBAgGBgUGCQgKCgkICQkKDA8MCgsOCwkJDRENDg8QEBEQCgwSExIQEw8QEBD/yQALCAABAAEBAREA/8wABgAQEAX/2gAIAQEAAD8A0s8g/9k=');
        $this->setStubResponse($data);
        $this->module->dontSeeBinaryResponseEquals('024f615102cdb3c8c7cf75cdc5a83d15');
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
