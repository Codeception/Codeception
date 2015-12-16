<?php
namespace Codeception\Util;


class JsonArrayTest extends \Codeception\TestCase\Test
{

    /**
     * @var JsonArray
     */
    protected $jsonArray;

    protected function _before()
    {
        $this->jsonArray = new JsonArray('{"ticket": {"title": "Bug should be fixed", "user": {"name": "Davert"}, "labels": null}}');
    }

    // tests
    public function testInclusion()
    {
        $this->assertTrue($this->jsonArray->containsArray(['name' => 'Davert']));
        $this->assertTrue($this->jsonArray->containsArray(['user' => ['name' => 'Davert']]));
        $this->assertTrue($this->jsonArray->containsArray(['ticket' => ['title' => 'Bug should be fixed']]));
        $this->assertTrue($this->jsonArray->containsArray(['ticket' => ['user' => ['name' => 'Davert']]]));
        $this->assertTrue($this->jsonArray->containsArray(['ticket' => ['labels' => null]]));
    }

    public function testXmlConversion()
    {
        $this->assertContains('<ticket><title>Bug should be fixed</title><user><name>Davert</name></user><labels></labels></ticket>',
            $this->jsonArray->toXml()->saveXML());
    }

    public function testXmlArrayConversion2()
    {
        $jsonArray = new JsonArray('[{"user":"Blacknoir","age":27,"tags":["wed-dev","php"]},{"user":"John Doe","age":27,"tags":["web-dev","java"]}]');
        $this->assertContains('<tags>wed-dev</tags>', $jsonArray->toXml()->saveXML());
        $this->assertEquals(2, $jsonArray->filterByXPath('//user')->length);
    }

    public function testXPathLocation()
    {
        $this->assertTrue($this->jsonArray->filterByXPath('//ticket/title')->length > 0);
        $this->assertTrue($this->jsonArray->filterByXPath('//ticket/user/name')->length > 0);
        $this->assertTrue($this->jsonArray->filterByXPath('//user/name')->length > 0);
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
     * @Issue https://github.com/Codeception/Codeception/issues/2070
     */
    public function testContainsArrayComparesArrayWithMultipleZeroesCorrectly()
    {
        $jsonArray = new JsonArray(json_encode([
            'responseCode' => 0,
            'message' => 'OK',
            'data' => [9, 0, 0],
        ]));

        $expectedArray = [
            'responseCode' => 0,
            'message' => 'OK',
            'data' => [0, 0, 0],
        ];

        $this->assertFalse($jsonArray->containsArray($expectedArray));
    }

    public function testContainsArrayComparesArrayWithMultipleIdenticalSubArraysCorrectly()
    {
        $jsonArray = new JsonArray(json_encode([
            'responseCode' => 0,
            'message' => 'OK',
            'data' => [[9], [0], [0]],
        ]));

        $expectedArray = [
            'responseCode' => 0,
            'message' => 'OK',
            'data' => [[0], [0], [0]],
        ];

        $this->assertFalse($jsonArray->containsArray($expectedArray));
    }

    public function testContainsArrayComparesArrayWithValueRepeatedMultipleTimesCorrectlyNegativeCase()
    {
        $jsonArray = new JsonArray(json_encode(['foo', 'foo', 'bar']));
        $expectedArray = ['foo', 'foo', 'foo'];
        $this->assertFalse($jsonArray->containsArray($expectedArray));
    }

    public function testContainsArrayComparesArrayWithValueRepeatedMultipleTimesCorrectlyPositiveCase()
    {
        $jsonArray = new JsonArray(json_encode(['foo', 'foo', 'bar']));
        $expectedArray = ['foo', 'bar', 'foo'];
        $this->assertTrue($jsonArray->containsArray($expectedArray));
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/2535
     */
    public function testThrowsInvalidArgumentExceptionIfJsonIsInvalid()
    {
        $this->setExpectedException('InvalidArgumentException');
        new JsonArray('{"test":');
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/2630
     */
    public function testContainsArrayComparesNestedSequentialArraysCorrectlyWhenSecondValueIsTheSame()
    {
        $jsonArray = new JsonArray('[
            [
                "2015-09-10",
                "unknown-date-1"
            ],
            [
                "2015-10-10",
                "unknown-date-1"
            ]
        ]');
        $expectedArray = [
            ["2015-09-10", "unknown-date-1"],
            ["2015-10-10", "unknown-date-1"],
        ];
        $this->assertTrue($jsonArray->containsArray($expectedArray));
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/2630
     */
    public function testContainsArrayComparesNestedSequentialArraysCorrectlyWhenSecondValueIsTheSameButOrderOfItemsIsDifferent()
    {
        $jsonArray = new JsonArray('[
            [
                "2015-09-10",
                "unknown-date-1"
            ],
            [
                "2015-10-10",
                "unknown-date-1"
            ]
        ]');
        $expectedArray = [
            ["2015-10-10", "unknown-date-1"],
            ["2015-09-10", "unknown-date-1"],
        ];
        $this->assertTrue($jsonArray->containsArray($expectedArray));
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/2630
     */
    public function testContainsArrayComparesNestedSequentialArraysCorrectlyWhenSecondValueIsDifferent()
    {
        $jsonArray = new JsonArray('[
            [
                "2015-09-10",
                "unknown-date-1"
            ],
            [
                "2015-10-10",
                "unknown-date-2"
            ]
        ]');
        $expectedArray = [
            ["2015-09-10", "unknown-date-1"],
            ["2015-10-10", "unknown-date-2"],
        ];
        $this->assertTrue($jsonArray->containsArray($expectedArray));
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/2630
     */
    public function testContainsArrayComparesNestedSequentialArraysCorrectlyWhenJsonHasMoreItemsThanExpectedArray()
    {
        $jsonArray = new JsonArray('[
            [
                "2015-09-10",
                "unknown-date-1"
            ],
            [
                "2015-10-02",
                "unknown-date-1"
            ],
            [
                "2015-10-10",
                "unknown-date-2"
            ]
        ]');
        $expectedArray = [
            ["2015-09-10", "unknown-date-1"],
            ["2015-10-10", "unknown-date-2"],
        ];
        $this->assertTrue($jsonArray->containsArray($expectedArray));
    }

    /**
     * @issue https://github.com/Codeception/Codeception/pull/2635
     */
    public function testContainsMatchesSuperSetOfExpectedAssociativeArrayInsideSequentialArray()
    {
        $jsonArray = new JsonArray(json_encode([[
                'id' => '1',
                'title' => 'Game of Thrones',
                'body' => 'You are so awesome',
                'created_at' => '2015-12-16 10:42:20',
                'updated_at' => '2015-12-16 10:42:20',
            ]]));
        $expectedArray = [['id' => '1']];
        $this->assertTrue($jsonArray->containsArray($expectedArray));
    }
}
