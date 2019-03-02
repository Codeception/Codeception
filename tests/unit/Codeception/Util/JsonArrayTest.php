<?php
namespace Codeception\Util;

class JsonArrayTest extends \Codeception\Test\Unit
{

    /**
     * @var JsonArray
     */
    protected $jsonArray;

    protected function _before()
    {
        $this->jsonArray = new JsonArray(
            '{"ticket": {"title": "Bug should be fixed", "user": {"name": "Davert"}, "labels": null}}'
        );
    }

    public function testXmlConversion()
    {
        $this->assertStringContainsString(
            '<ticket><title>Bug should be fixed</title><user><name>Davert</name></user><labels></labels></ticket>',
            $this->jsonArray->toXml()->saveXML()
        );
    }

    public function testXmlArrayConversion2()
    {
        $jsonArray = new JsonArray(
            '[{"user":"Blacknoir","age":27,"tags":["wed-dev","php"]},'
            . '{"user":"John Doe","age":27,"tags":["web-dev","java"]}]'
        );
        $this->assertStringContainsString('<tags>wed-dev</tags>', $jsonArray->toXml()->saveXML());
        $this->assertEquals(2, $jsonArray->filterByXPath('//user')->length);
    }

    public function testXPathLocation()
    {
        $this->assertGreaterThan(0, $this->jsonArray->filterByXPath('//ticket/title')->length);
        $this->assertGreaterThan(0, $this->jsonArray->filterByXPath('//ticket/user/name')->length);
        $this->assertGreaterThan(0, $this->jsonArray->filterByXPath('//user/name')->length);
    }

    public function testJsonPathLocation()
    {
        $this->assertNotEmpty($this->jsonArray->filterByJsonPath('$..user'));
        $this->assertNotEmpty($this->jsonArray->filterByJsonPath('$.ticket.user.name'));
        $this->assertNotEmpty($this->jsonArray->filterByJsonPath('$..user.name'));
        $this->assertEquals(['Davert'], $this->jsonArray->filterByJsonPath('$.ticket.user.name'));
        $this->assertEmpty($this->jsonArray->filterByJsonPath('$..invalid'));
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/2535
     */
    public function testThrowsInvalidArgumentExceptionIfJsonIsInvalid()
    {
        $this->expectException('InvalidArgumentException');
        new JsonArray('{"test":');
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/4944
     */
    public function testConvertsBareJson()
    {
        $jsonArray = new JsonArray('"I am a {string}."');
        $this->assertEquals(['I am a {string}.'], $jsonArray->toArray());
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/issues/2899
     */
    public function testInvalidXmlTag()
    {
        $jsonArray = new JsonArray('{"a":{"foo/bar":1,"":2},"b":{"foo/bar":1,"":2},"baz":2}');
        $expectedXml = '<a><invalidTag1>1</invalidTag1><invalidTag2>2</invalidTag2></a>'
            . '<b><invalidTag1>1</invalidTag1><invalidTag2>2</invalidTag2></b><baz>2</baz>';
        $this->assertStringContainsString($expectedXml, $jsonArray->toXml()->saveXML());
    }

    public function testConvertsArrayHavingSingleElement()
    {
        $jsonArray = new JsonArray('{"success": 1}');
        $expectedXml = '<?xml version="1.0" encoding="UTF-8"?>'
            . "\n<root><success>1</success></root>\n";
        $this->assertEquals($expectedXml, $jsonArray->toXml()->saveXML());
    }

    public function testConvertsArrayHavingTwoElements()
    {
        $jsonArray = new JsonArray('{"success": 1, "info": "test"}');
        $expectedXml = '<?xml version="1.0" encoding="UTF-8"?>'
            . "\n<root><success>1</success><info>test</info></root>\n";
        $this->assertEquals($expectedXml, $jsonArray->toXml()->saveXML());
    }

    public function testConvertsArrayHavingSingleSubArray()
    {
        $jsonArray = new JsonArray('{"array": {"success": 1}}');
        $expectedXml = '<?xml version="1.0" encoding="UTF-8"?>'
            . "\n<array><success>1</success></array>\n";
        $this->assertEquals($expectedXml, $jsonArray->toXml()->saveXML());
    }
}
