<?php
namespace Codeception\Util;

class JsonTypeTest extends \Codeception\TestCase\Test
{
    protected $types = [
        'id' => 'integer:>10',
        'retweeted' => 'Boolean',
        'in_reply_to_screen_name' => 'null|string',
        'user' => [
          'url' => 'String:url'
        ]
    ];
    protected $data = [
        'id' => 11,
        'retweeted' => false,
        'in_reply_to_screen_name' => null,
        'user' => ['url' => 'http://davert.com']
    ];

    public function _after()
    {
        JsonType::cleanCustomFilters();
    }

    public function testMatchBasicTypes()
    {
        $jsonType = new JsonType($this->data);
        $this->assertTrue($jsonType->matches($this->types));
    }

    public function testNotMatchesBasicType()
    {
        $this->data['in_reply_to_screen_name'] = true;
        $jsonType = new JsonType($this->data);
        $this->assertContains('`in_reply_to_screen_name: true` is not of type', $jsonType->matches($this->types));
    }

    public function testIntegerFilter()
    {
        $jsonType = new JsonType($this->data);
        $this->assertContains('`id: 11` is not of type', $jsonType->matches(['id' => 'integer:<5']));
        $this->assertContains('`id: 11` is not of type', $jsonType->matches(['id' => 'integer:>15']));
        $this->assertTrue($jsonType->matches(['id' => 'integer:=11']));
        $this->assertTrue($jsonType->matches(['id' => 'integer:>5']));
        $this->assertTrue($jsonType->matches(['id' => 'integer:>5:<12']));
        $this->assertNotTrue($jsonType->matches(['id' => 'integer:>5:<10']));
    }

    public function testUrlFilter()
    {
        $this->data['user']['url'] = 'invalid_url';
        $jsonType = new JsonType($this->data);
        $this->assertNotTrue($jsonType->matches($this->types));
    }

    public function testRegexFilter()
    {
        $jsonType = new JsonType(['numbers' => '1-2-3']);
        $this->assertTrue($jsonType->matches(['numbers' => 'string:regex(~1-2-3~)']));
        $this->assertTrue($jsonType->matches(['numbers' => 'string:regex(~\d-\d-\d~)']));
        $this->assertNotTrue($jsonType->matches(['numbers' => 'string:regex(~^\d-\d$~)']));
    }

    public function testDateTimeFilter()
    {
        $jsonType = new JsonType(['date' => '2011-11-30T04:06:44Z']);
        $this->assertTrue($jsonType->matches(['date' => 'string:date']));
    }

    public function testNegativeFilters()
    {
        $jsonType = new JsonType(['name' => 'davert', 'id' => 1]);
        $this->assertTrue($jsonType->matches([
            'name' => 'string:!date|string:!empty',
            'id' => 'integer:!=0',
        ]));
    }

    public function testCustomFilters()
    {
        JsonType::addCustomFilter('email', function($value) {
            return strpos($value, '@') !== false;
        });
        $jsonType = new JsonType(['email' => 'davert@codeception.com', 'name' => 'davert']);
        $this->assertTrue($jsonType->matches([
            'email' => 'string:email'
        ]));
        $this->assertNotTrue($jsonType->matches([
            'name' => 'string:email'
        ]));

        JsonType::addCustomFilter('/len\((.*?)\)/', function($value, $len) {
            return strlen($value) == $len;
        });
        $this->assertTrue($jsonType->matches([
            'email' => 'string:len(22)'
        ]));
        $this->assertNotTrue($jsonType->matches([
            'email' => 'string:len(7)'
        ]));
    }

    public function testArray()
    {
        $this->types['user'] = 'array';
        $jsonType = new JsonType($this->data);
        $this->assertTrue($jsonType->matches($this->types));
    }
}