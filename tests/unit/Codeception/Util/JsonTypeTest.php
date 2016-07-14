<?php
namespace Codeception\Util;

class JsonTypeTest extends \Codeception\Test\Unit
{
    protected $types = [
        'id' => 'integer:>10',
        'retweeted' => 'Boolean',
        'in_reply_to_screen_name' => 'null|string',
        'name' => 'string|null', // http://codeception.com/docs/modules/REST#seeResponseMatchesJsonType
        'user' => [
            'url' => 'String:url'
        ]
    ];
    protected $data = [
        'id' => 11,
        'retweeted' => false,
        'in_reply_to_screen_name' => null,
        'name' => null,
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
        $this->assertContains('`in_reply_to_screen_name: true` is of type', $jsonType->matches($this->types));
    }

    public function testIntegerFilter()
    {
        $jsonType = new JsonType($this->data);
        $this->assertContains('`id: 11` is of type', $jsonType->matches(['id' => 'integer:<5']));
        $this->assertContains('`id: 11` is of type', $jsonType->matches(['id' => 'integer:>15']));
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
        
        $jsonType = new JsonType(['published' => 1]);
        $this->assertTrue($jsonType->matches(['published' => 'integer:regex(~1~)']));
        $this->assertTrue($jsonType->matches(['published' => 'integer:regex(~1|2~)']));
        $this->assertTrue($jsonType->matches(['published' => 'integer:regex(~2|1~)']));
        $this->assertNotTrue($jsonType->matches(['published' => 'integer:regex(~2~)']));
        $this->assertNotTrue($jsonType->matches(['published' => 'integer:regex(~2|3~)']));
        $this->assertNotTrue($jsonType->matches(['published' => 'integer:regex(~3|2~)']));

        $jsonType = new JsonType(['date' => '2011-11-30T04:06:44Z']);
        $this->assertTrue($jsonType->matches(['date' => 'string:regex(~2011-11-30T04:06:44Z|2011-11-30T05:07:00Z~)']));
        $this->assertNotTrue(
            $jsonType->matches(['date' => 'string:regex(~2015-11-30T04:06:44Z|2016-11-30T05:07:00Z~)'])
        );
    }

    public function testDateTimeFilter()
    {
        $jsonType = new JsonType(['date' => '2011-11-30T04:06:44Z']);
        $this->assertTrue($jsonType->matches(['date' => 'string:date']));
        $jsonType = new JsonType(['date' => '2012-04-30T04:06:00.123Z']);
        $this->assertTrue($jsonType->matches(['date' => 'string:date']));
        $jsonType = new JsonType(['date' => '1931-01-05T04:06:03.1+05:30']);
        $this->assertTrue($jsonType->matches(['date' => 'string:date']));
    }

    public function testEmailFilter()
    {
        $jsonType = new JsonType(['email' => 'davert@codeception.com']);
        $this->assertTrue($jsonType->matches(['email' => 'string:email']));
        $jsonType = new JsonType(['email' => 'davert.codeception.com']);
        $this->assertNotTrue($jsonType->matches(['email' => 'string:email']));
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
        JsonType::addCustomFilter('slug', function ($value) {
            return strpos($value, ' ') === false;
        });
        $jsonType = new JsonType(['title' => 'have a test', 'slug' => 'have-a-test']);
        $this->assertTrue($jsonType->matches([
            'slug' => 'string:slug'
        ]));
        $this->assertNotTrue($jsonType->matches([
            'title' => 'string:slug'
        ]));

        JsonType::addCustomFilter('/len\((.*?)\)/', function ($value, $len) {
            return strlen($value) == $len;
        });
        $this->assertTrue($jsonType->matches([
            'slug' => 'string:len(11)'
        ]));
        $this->assertNotTrue($jsonType->matches([
            'slug' => 'string:len(7)'
        ]));
    }

    public function testArray()
    {
        $this->types['user'] = 'array';
        $jsonType = new JsonType($this->data);
        $this->assertTrue($jsonType->matches($this->types));
    }

    public function testNull()
    {
        $jsonType = new JsonType(json_decode('{
            "id": 123456,
            "birthdate": null,
            "firstname": "John",
            "lastname": "Doe"
        }', true));
        $this->assertTrue($jsonType->matches([
            'birthdate' => 'string|null'
        ]));
        $this->assertTrue($jsonType->matches([
            'birthdate' => 'null'
        ]));
    }

    public function testOR()
    {
        $jsonType = new JsonType(json_decode('{
            "type": "DAY"
        }', true));
        $this->assertTrue($jsonType->matches([
            'type' => 'string:=DAY|string:=WEEK'
        ]));
        $jsonType = new JsonType(json_decode('{
            "type": "WEEK"
        }', true));
        $this->assertTrue($jsonType->matches([
            'type' => 'string:=DAY|string:=WEEK'
        ]));
    }

    public function testCollection()
    {
        $jsonType = new JsonType([
            ['id' => 1],
            ['id' => 3],
            ['id' => 5]
        ]);
        $this->assertTrue($jsonType->matches([
            'id' => 'integer'
        ]));

        $this->assertNotTrue($res = $jsonType->matches([
            'id' => 'integer:<3'
        ]));
        
        $this->assertContains('3` is of type `integer:<3', $res);
        $this->assertContains('5` is of type `integer:<3', $res);
    }
}
