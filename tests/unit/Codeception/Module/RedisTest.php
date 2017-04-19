<?php

use Codeception\Lib\ModuleContainer;
use Codeception\Module\Redis;
use Codeception\Test\Unit;

class RedisTest extends Unit
{
    /**
     * @var array
     */
    protected static $config = [
        'database' => 15
    ];

    /**
     * @var Redis
     */
    protected $module;

    /**
     * Keys that will be created for the tests
     *
     * @var array
     */
    protected static $keys = [
        'string' => [
            'name' => 'test:string',
            'value' => 'hello'
        ],
        'list' => [
            'name' => 'test:list',
            'value' => ['riri', 'fifi', 'loulou']
        ],
        'set' => [
            'name' => 'test:set',
            'value' => ['huey', 'dewey', 'louie']
        ],
        'zset' => [
            'name' => 'test:zset',
            'value' => ['juanito' => 1, 'jorgito' => 2, 'jaimito' => 3]
        ],
        'hash' => [
            'name' => 'test:hash',
            'value' => ['Tick' => true, 'Trick' => 'dewey', 'Track' => 42]
        ]
    ];


    /**
     * {@inheritdoc}
     *
     * Every time a test starts, cleanup the database and populates it with some
     * dummy data.
     */
    protected function setUp()
    {
        if (!class_exists('Predis\Client')) {
            $this->markTestSkipped('Predis is not installed');
        }
        /** @var ModuleContainer $container */
        $container = make_container();

        try {
            $this->module = new Redis($container);
            $this->module->_setConfig(self::$config);
            $this->module->_initialize();

            $this->module->driver->flushDb();
        } catch (Predis\Connection\ConnectionException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $addMethods = [
            'string' => 'set',
            'list' => 'rPush',
            'set' => 'sAdd',
            'zset' => 'zAdd',
            'hash' => 'hMSet'
        ];
        foreach (self::$keys as $type => $key) {
            $this->module->driver->{$addMethods[$type]}(
                $key['name'],
                $key['value']
            );
        }
    }

    /**
     * Indicates that the next test is expected to fail
     * @param null $exceptionClass The fully qualified class name of the
     * expected exception
     */
    protected function shouldFail($exceptionClass = null)
    {
        if (!$exceptionClass) {
            $exceptionClass = 'PHPUnit_Framework_AssertionFailedError';
        }

        $this->setExpectedException($exceptionClass);
    }

    // ****************************************
    // Test grabFromRedis() with non existing keys
    // ****************************************

    public function testGrabFromRedisNonExistingKey()
    {
        $this->shouldFail('\Codeception\Exception\ModuleException');
        $this->module->grabFromRedis('doesnotexist');
    }

    // *******************************
    // Test grabFromRedis() with Strings
    // *******************************

    public function testGrabFromRedisString()
    {
        $result = $this->module->grabFromRedis(self::$keys['string']['name']);
        $this->assertSame(
            self::$keys['string']['value'],
            $result
        );
    }

    // *******************************
    // Test grabFromRedis() with Lists
    // *******************************

    public function testGrabFromRedisListMember()
    {
        $index = 2;
        $result = $this->module->grabFromRedis(
            self::$keys['list']['name'],
            $index
        );
        $this->assertSame(
            self::$keys['list']['value'][$index],
            $result
        );
    }

    public function testGrabFromRedisListRange()
    {
        $rangeFrom = 1;
        $rangeTo = 2;
        $result = $this->module->grabFromRedis(
            self::$keys['list']['name'],
            $rangeFrom,
            $rangeTo
        );
        $this->assertSame(
            array_slice(
                self::$keys['list']['value'],
                $rangeFrom,
                $rangeTo - $rangeFrom + 1
            ),
            $result
        );
    }

    // *******************************
    // Test grabFromRedis() with Sets
    // *******************************

    public function testGrabFromRedisSet()
    {
        $result = $this->module->grabFromRedis(
            self::$keys['set']['name']
        );
        sort($result);

        $reference = self::$keys['set']['value'];
        sort($reference);

        $this->assertSame($reference, $result);
    }

    // *******************************
    // Test grabFromRedis() with Sorted Sets
    // *******************************

    public function testGrabFromRedisZSetWithTwoArguments()
    {
        $this->shouldFail('\Codeception\Exception\ModuleException');
        $this->module->grabFromRedis(
            self::$keys['zset']['name'],
            1
        );
    }

    public function testGrabFromRedisZSetAll()
    {
        $expected = self::$keys['zset']['value'];
        $result = $this->module->grabFromRedis(self::$keys['zset']['name']);

        $this->assertSame(
            $this->scoresToFloat($expected),
            $this->scoresToFloat($result)
        );
    }

    public function testGrabFromRedisZSetRange()
    {
        $rangeFrom = 1;
        $rangeTo = 2;

        $expected = array_slice(
            self::$keys['zset']['value'],
            $rangeFrom,
            ($rangeTo - $rangeFrom + 1)
        );

        $result = $this->module->grabFromRedis(
            self::$keys['zset']['name'],
            $rangeFrom,
            $rangeTo
        );

        $this->assertSame(
            $this->scoresToFloat($expected),
            $this->scoresToFloat($result)
        );
    }

    // *******************************
    // Test grabFromRedis() with Hashes
    // *******************************

    public function testGrabFromRedisHashAll()
    {
        $result = $this->module->grabFromRedis(
            self::$keys['hash']['name']
        );

        $this->assertEquals(
            $this->boolToString(self::$keys['hash']['value']),
            $result
        );
    }

    public function testGrabFromRedisHashField()
    {
        $field = 'Trick';

        $result = $this->module->grabFromRedis(
            self::$keys['hash']['name'],
            $field
        );

        $this->assertSame(
            self::$keys['hash']['value'][$field],
            $result
        );
    }

    // *******************************
    // Test haveInRedis() with Strings
    // *******************************

    public function testHaveInRedisNonExistingString()
    {
        $newKey = [
            'name' => 'test:string-create',
            'value' => 'testing string creation'
        ];
        $this->module->haveInRedis(
            'string',
            $newKey['name'],
            $newKey['value']
        );
        $this->assertSame(
            $newKey['value'],
            $this->module->driver->get($newKey['name'])
        );
    }

    public function testHaveInRedisExistingString()
    {
        $newValue = 'new value';
        $this->module->haveInRedis(
            'string',
            self::$keys['string']['name'],
            $newValue
        );
        $this->assertSame(
            $newValue,
            $this->module->driver->get(self::$keys['string']['name'])
        );
    }

    // *******************************
    // Test haveInRedis() with Lists
    // *******************************

    public function testHaveInRedisNonExistingListArrayArg()
    {
        $newKey = [
            'name' => 'test:list-create-array',
            'value' => ['testing', 'list', 'creation']
        ];
        $this->module->haveInRedis(
            'list',
            $newKey['name'],
            $newKey['value']
        );
        $this->assertSame(
            $newKey['value'],
            $this->module->driver->lrange($newKey['name'], 0, -1)
        );
    }

    public function testHaveInRedisNonExistingListScalarArg()
    {
        $newKey = [
            'name' => 'test:list-create-scalar',
            'value' => 'testing list creation'
        ];
        $this->module->haveInRedis(
            'list',
            $newKey['name'],
            $newKey['value']
        );
        $this->assertSame(
            [$newKey['value']],
            $this->module->driver->lrange($newKey['name'], 0, -1)
        );
    }

    public function testHaveInRedisExistingListArrayArg()
    {
        $newValue = ['testing', 'list', 'creation'];

        $this->module->haveInRedis(
            'list',
            self::$keys['list']['name'],
            $newValue
        );
        $this->assertSame(
            array_merge(
                self::$keys['list']['value'],
                $newValue
            ),
            $this->module->driver->lrange(self::$keys['list']['name'], 0, -1)
        );
    }

    public function testHaveInRedisExistingListArrayScalar()
    {
        $newValue = 'testing list creation';

        $this->module->haveInRedis(
            'list',
            self::$keys['list']['name'],
            $newValue
        );
        $this->assertSame(
            array_merge(
                self::$keys['list']['value'],
                [$newValue]
            ),
            $this->module->driver->lrange(self::$keys['list']['name'], 0, -1)
        );
    }

    // *******************************
    // Test haveInRedis() with Sets
    // *******************************

    public function testHaveInRedisNonExistingSetArrayArg()
    {
        $newKey = [
            'name' => 'test:set-create-array',
            'value' => ['testing', 'set', 'creation']
        ];
        $this->module->haveInRedis(
            'set',
            $newKey['name'],
            $newKey['value']
        );

        $expected = $newKey['value'];
        sort($expected);

        $result = $this->module->driver->sMembers($newKey['name']);
        sort($result);

        $this->assertSame($expected, $result);
    }

    public function testHaveInRedisNonExistingSetScalarArg()
    {
        $newKey = [
            'name' => 'test:set-create-scalar',
            'value' => 'testing set creation'
        ];
        $this->module->haveInRedis(
            'set',
            $newKey['name'],
            $newKey['value']
        );
        $this->assertSame(
            [$newKey['value']],
            $this->module->driver->sMembers($newKey['name'])
        );
    }

    public function testHaveInRedisExistingSetArrayArg()
    {
        $newValue = ['testing', 'set', 'creation'];

        $this->module->haveInRedis(
            'set',
            self::$keys['set']['name'],
            $newValue
        );
        $expectedValue = array_merge(
            self::$keys['set']['value'],
            $newValue
        );
        sort($expectedValue);

        $result = $this->module->driver->sMembers(self::$keys['set']['name']);
        sort($result);

        $this->assertSame($expectedValue, $result);
    }

    public function testHaveInRedisExistingSetArrayScalar()
    {
        $newValue = 'testing set creation';

        $this->module->haveInRedis(
            'set',
            self::$keys['set']['name'],
            $newValue
        );

        $expectedResult = array_merge(
            self::$keys['set']['value'],
            [$newValue]
        );
        sort($expectedResult);

        $result = $this->module->driver->sMembers(self::$keys['set']['name']);
        sort($result);

        $this->assertSame($expectedResult, $result);
    }

    // *******************************
    // Test haveInRedis() with Sorted sets
    // *******************************

    public function testHaveInRedisZSetScalar()
    {
        $this->shouldFail('\Codeception\Exception\ModuleException');
        $this->module->haveInRedis(
            'zset',
            'test:zset-create-array',
            'foobar'
        );
    }

    public function testHaveInRedisNonExistingZSetArrayArg()
    {
        $newKey = [
            'name' => 'test:zset-create-array',
            'value' => [
                'testing' => 2,
                'zset' => 1,
                'creation' => 2,
                'foo' => 3
            ]
        ];
        $this->module->haveInRedis(
            'zset',
            $newKey['name'],
            $newKey['value']
        );

        $result = $this->module->driver->zrange($newKey['name'], 0, -1, 'WITHSCORES');

        $this->assertSame(
            ['zset' => 1.0, 'creation' => 2.0, 'testing' => 2.0, 'foo' => 3.0],
            $this->scoresToFloat($result)
        );
    }

    public function testHaveInRedisExistingZSetArrayArg()
    {
        $newValue = [
            'testing' => 2,
            'zset' => 1,
            'creation' => 2,
            'foo' => 3
        ];

        $this->module->haveInRedis(
            'zset',
            self::$keys['zset']['name'],
            $newValue
        );

        $expected = array_merge(
            self::$keys['zset']['value'],
            $newValue
        );
        array_multisort(
            array_values($expected),
            SORT_ASC,
            array_keys($expected),
            SORT_ASC,
            $expected
        );

        $result = $this->module->driver->zRange(
            self::$keys['zset']['name'],
            0,
            -1,
            'WITHSCORES'
        );

        $this->assertSame(
            $this->scoresToFloat($expected),
            $this->scoresToFloat($result)
        );
    }

    // *******************************
    // Test haveInRedis() with Hashes
    // *******************************

    public function testHaveInRedisHashScalar()
    {
        $this->shouldFail('\Codeception\Exception\ModuleException');
        $this->module->haveInRedis(
            'hash',
            'test:hash-create-array',
            'foobar'
        );
    }

    public function testHaveInRedisNonExistingHashArrayArg()
    {
        $newKey = [
            'name' => 'test:hash-create-array',
            'value' => [
                'obladi' => 'oblada',
                'nope' => false,
                'zero' => 0
            ]
        ];
        $this->module->haveInRedis(
            'hash',
            $newKey['name'],
            $this->boolToString($newKey['value'])
        );
        $this->assertEquals(
            $this->boolToString($newKey['value']),
            $this->module->driver->hGetAll($newKey['name'])
        );
    }

    public function testHaveInRedisExistingHashArrayArg()
    {
        $newValue = [
            'obladi' => 'oblada',
            'nope' => false,
            'zero' => 0
        ];
        $this->module->haveInRedis(
            'hash',
            self::$keys['hash']['name'],
            $newValue
        );
        $this->assertEquals(
            array_merge(
                self::$keys['hash']['value'],
                $newValue
            ),
            $this->module->driver->hGetAll(self::$keys['hash']['name'])
        );
    }

    // ****************************************
    // Test dontSeeInRedis() with non existing keys
    // ****************************************

    public function testDontSeeInRedisNonExistingKeyWithoutValue()
    {
        $this->module->dontSeeInRedis('doesnotexist');
    }

    public function testDontSeeInRedisNonExistingKeyWithValue()
    {
        $this->module->dontSeeInRedis(
            'doesnotexist',
            'some value'
        );
    }

    // *******************************
    // Test dontSeeInRedis() without value
    // *******************************

    public function testDontSeeInRedisExistingKeyWithoutValue()
    {
        $this->shouldFail();
        $this->module->dontSeeInRedis(
            self::$keys['string']['name']
        );
    }

    // *******************************
    // Test dontSeeInRedis() with Strings
    // *******************************

    public function testDontSeeInRedisExistingStringWithCorrectValue()
    {
        $this->shouldFail();
        $this->module->dontSeeInRedis(
            self::$keys['string']['name'],
            self::$keys['string']['value']
        );
    }

    public function testDontSeeInRedisExistingStringWithIncorrectValue()
    {
        $this->module->dontSeeInRedis(
            self::$keys['string']['name'],
            'incorrect value'
        );
    }

    // *******************************
    // Test dontSeeInRedis() with Lists
    // *******************************

    public function testDontSeeInRedisExistingListWithCorrectValue()
    {
        $this->shouldFail();
        $this->module->dontSeeInRedis(
            self::$keys['list']['name'],
            self::$keys['list']['value']
        );
    }

    public function testDontSeeInRedisExistingListWithCorrectValueDifferentOrder()
    {
        $this->module->dontSeeInRedis(
            self::$keys['list']['name'],
            array_reverse(self::$keys['list']['value'])
        );
    }

    public function testDontSeeInRedisExistingListWithIncorrectValue()
    {
        $this->module->dontSeeInRedis(
            self::$keys['list']['name'],
            ['incorrect', 'value']
        );
    }

    // *******************************
    // Test dontSeeInRedis() with Sets
    // *******************************

    public function testDontSeeInRedisExistingSetWithCorrectValue()
    {
        $this->shouldFail();
        $this->module->dontSeeInRedis(
            self::$keys['set']['name'],
            self::$keys['set']['value']
        );
    }

    public function testDontSeeInRedisExistingSetWithCorrectValueDifferentOrder()
    {
        $this->shouldFail();
        $this->module->dontSeeInRedis(
            self::$keys['set']['name'],
            array_reverse(self::$keys['set']['value'])
        );
    }

    public function testDontSeeInRedisExistingSetWithIncorrectValue()
    {
        $this->module->dontSeeInRedis(
            self::$keys['set']['name'],
            ['incorrect', 'value']
        );
    }

    // *******************************
    // Test dontSeeInRedis() with Sorted Sets
    // *******************************

    public function testDontSeeInRedisExistingZSetWithCorrectValue()
    {
        $this->shouldFail();
        $this->module->dontSeeInRedis(
            self::$keys['zset']['name'],
            self::$keys['zset']['value']
        );
    }

    public function testDontSeeInRedisExistingZSetWithCorrectValueWithoutScores()
    {
        $this->module->dontSeeInRedis(
            self::$keys['zset']['name'],
            array_keys(self::$keys['zset']['value'])
        );
    }

    public function testDontSeeInRedisExistingZSetWithCorrectValueDifferentOrder()
    {
        $this->module->dontSeeInRedis(
            self::$keys['zset']['name'],
            array_reverse(self::$keys['zset']['value'])
        );
    }

    public function testDontSeeInRedisExistingZSetWithIncorrectValue()
    {
        $this->module->dontSeeInRedis(
            self::$keys['zset']['name'],
            ['incorrect' => 1, 'value' => 2]
        );
    }

    // *******************************
    // Test dontSeeInRedis() with Hashes
    // *******************************

    public function testDontSeeInRedisExistingHashWithCorrectValue()
    {
        $this->shouldFail();
        $this->module->dontSeeInRedis(
            self::$keys['hash']['name'],
            self::$keys['hash']['value']
        );
    }

    public function testDontSeeInRedisExistingHashWithCorrectValueDifferentOrder()
    {
        $this->shouldFail();
        $this->module->dontSeeInRedis(
            self::$keys['hash']['name'],
            array_reverse(self::$keys['hash']['value'])
        );
    }

    public function testDontSeeInRedisExistingHashWithIncorrectValue()
    {
        $this->module->dontSeeInRedis(
            self::$keys['hash']['name'],
            ['incorrect' => 'value']
        );
    }

    // ****************************************
    // Test dontSeeRedisKeyContains() with non existing keys
    // ****************************************

    public function testDontSeeRedisKeyContainsNonExistingKey()
    {
        $this->shouldFail('\Codeception\Exception\ModuleException');
        $this->module->dontSeeRedisKeyContains('doesnotexist', 'doesnotexist');
    }

    // ****************************************
    // Test dontSeeRedisKeyContains() with array args
    // ****************************************

    public function testDontSeeRedisKeyContainsWithArrayArgs()
    {
        $this->shouldFail('\Codeception\Exception\ModuleException');
        $this->module->dontSeeRedisKeyContains(
            self::$keys['hash']['name'],
            self::$keys['hash']['value']
        );
    }

    // *******************************
    // Test dontSeeRedisKeyContains() with Strings
    // *******************************

    public function testDontSeeRedisKeyContainsStringWithCorrectSubstring()
    {
        $this->shouldFail();
        $this->module->dontSeeRedisKeyContains(
            self::$keys['string']['name'],
            substr(self::$keys['string']['value'], 2, -2)
        );
    }

    public function testDontSeeRedisKeyContainsStringWithIncorrectValue()
    {
        $this->module->dontSeeRedisKeyContains(
            self::$keys['string']['name'],
            'incorrect string'
        );
    }

    // *******************************
    // Test dontSeeRedisKeyContains() with Lists
    // *******************************

    public function testDontSeeRedisKeyContainsListWithCorrectItem()
    {
        $this->shouldFail();
        $this->module->dontSeeRedisKeyContains(
            self::$keys['list']['name'],
            self::$keys['list']['value'][1]
        );
    }

    public function testDontSeeRedisKeyContainsListWithIncorrectItem()
    {
        $this->module->dontSeeRedisKeyContains(
            self::$keys['list']['name'],
            'incorrect'
        );
    }

    // *******************************
    // Test dontSeeRedisKeyContains() with Sets
    // *******************************

    public function testDontSeeRedisKeyContainsSetWithCorrectItem()
    {
        $this->shouldFail();
        $this->module->dontSeeRedisKeyContains(
            self::$keys['set']['name'],
            self::$keys['set']['value'][1]
        );
    }

    public function testDontSeeRedisKeyContainsSetWithIncorrectItem()
    {
        $this->module->dontSeeRedisKeyContains(
            self::$keys['set']['name'],
            'incorrect'
        );
    }

    // *******************************
    // Test dontSeeRedisKeyContains() with Sorted sets
    // *******************************

    public function testDontSeeRedisKeyContainsZSetWithCorrectItemWithScore()
    {
        $this->shouldFail();
        $firstItem = array_slice(self::$keys['zset']['value'], 0, 1);
        $firstMember = key($firstItem);
        $this->module->dontSeeRedisKeyContains(
            self::$keys['zset']['name'],
            $firstMember,
            $firstItem[$firstMember]
        );
    }

    public function testDontSeeRedisKeyContainsZSetWithCorrectItemWithIncorrectScore()
    {
        $firstItem = array_slice(self::$keys['zset']['value'], 0, 1);
        $firstKey = key($firstItem);
        $this->module->dontSeeRedisKeyContains(
            self::$keys['zset']['name'],
            $firstKey,
            'incorrect'
        );
    }

    public function testDontSeeRedisKeyContainsZSetWithCorrectItemWithoutScore()
    {
        $this->shouldFail();
        $arrayKeys = array_keys(self::$keys['zset']['value']);
        $this->module->dontSeeRedisKeyContains(
            self::$keys['zset']['name'],
            $arrayKeys[0]
        );
    }

    public function testDontSeeRedisKeyContainsZSetWithIncorrectItemWithoutScore()
    {
        $this->module->dontSeeRedisKeyContains(
            self::$keys['zset']['name'],
            'incorrect'
        );
    }

    public function testDontSeeRedisKeyContainsZSetWithIncorrectItemWithScore()
    {
        $this->module->dontSeeRedisKeyContains(
            self::$keys['zset']['name'],
            'incorrect',
            34
        );
    }

    // *******************************
    // Test dontSeeRedisKeyContains() with Hashes
    // *******************************

    public function testDontSeeRedisKeyContainsHashWithCorrectFieldWithValue()
    {
        $this->shouldFail();
        $firstField = array_slice(self::$keys['hash']['value'], 0, 1);
        $firstKey = key($firstField);
        $this->module->dontSeeRedisKeyContains(
            self::$keys['hash']['name'],
            $firstKey,
            $firstField[$firstKey]
        );
    }

    public function testDontSeeRedisKeyContainsHashWithCorrectFieldWithIncorrectValue()
    {
        $firstField = array_slice(self::$keys['hash']['value'], 0, 1);
        $firstKey = key($firstField);
        $this->module->dontSeeRedisKeyContains(
            self::$keys['hash']['name'],
            $firstKey,
            'incorrect'
        );
    }

    public function testDontSeeRedisKeyContainsHashWithCorrectFieldWithoutValue()
    {
        $this->shouldFail();
        $arrayKeys = array_keys(self::$keys['hash']['value']);
        $this->module->dontSeeRedisKeyContains(
            self::$keys['hash']['name'],
            $arrayKeys[0]
        );
    }

    public function testDontSeeRedisKeyContainsHashWithIncorrectFieldWithoutValue()
    {
        $this->module->dontSeeRedisKeyContains(
            self::$keys['hash']['name'],
            'incorrect'
        );
    }

    public function testDontSeeRedisKeyContainsHashWithIncorrectFieldWithValue()
    {
        $this->module->dontSeeRedisKeyContains(
            self::$keys['hash']['name'],
            'incorrect',
            34
        );
    }

    // ****************************************
    // Test seeInRedis() with non existing keys
    // ****************************************

    public function testSeeInRedisNonExistingKeyWithoutValue()
    {
        $this->shouldFail();
        $this->module->seeInRedis('doesnotexist');
    }

    public function testSeeInRedisNonExistingKeyWithValue()
    {
        $this->shouldFail();
        $this->module->seeInRedis(
            'doesnotexist',
            'some value'
        );
    }

    // *******************************
    // Test seeInRedis() without value
    // *******************************

    public function testSeeInRedisExistingKeyWithoutValue()
    {
        $this->module->seeInRedis(
            self::$keys['string']['name']
        );
    }

    // *******************************
    // Test seeInRedis() with Strings
    // *******************************

    public function testSeeInRedisExistingStringWithCorrectValue()
    {
        $this->module->seeInRedis(
            self::$keys['string']['name'],
            self::$keys['string']['value']
        );
    }

    public function testSeeInRedisExistingStringWithIncorrectValue()
    {
        $this->shouldFail();
        $this->module->seeInRedis(
            self::$keys['string']['name'],
            'incorrect value'
        );
    }

    // *******************************
    // Test seeInRedis() with Lists
    // *******************************

    public function testSeeInRedisExistingListWithCorrectValue()
    {
        $this->module->seeInRedis(
            self::$keys['list']['name'],
            self::$keys['list']['value']
        );
    }

    public function testSeeInRedisExistingListWithCorrectValueDifferentOrder()
    {
        $this->shouldFail();
        $this->module->seeInRedis(
            self::$keys['list']['name'],
            array_reverse(self::$keys['list']['value'])
        );
    }

    public function testSeeInRedisExistingListWithIncorrectValue()
    {
        $this->shouldFail();
        $this->module->seeInRedis(
            self::$keys['list']['name'],
            ['incorrect', 'value']
        );
    }

    // *******************************
    // Test seeInRedis() with Sets
    // *******************************

    public function testSeeInRedisExistingSetWithCorrectValue()
    {
        $this->module->seeInRedis(
            self::$keys['set']['name'],
            self::$keys['set']['value']
        );
    }

    public function testSeeInRedisExistingSetWithCorrectValueDifferentOrder()
    {
        $this->module->seeInRedis(
            self::$keys['set']['name'],
            array_reverse(self::$keys['set']['value'])
        );
    }

    public function testSeeInRedisExistingSetWithIncorrectValue()
    {
        $this->shouldFail();
        $this->module->seeInRedis(
            self::$keys['set']['name'],
            ['incorrect', 'value']
        );
    }

    // *******************************
    // Test seeInRedis() with Sorted Sets
    // *******************************

    public function testSeeInRedisExistingZSetWithCorrectValueWithScores()
    {
        $this->module->seeInRedis(
            self::$keys['zset']['name'],
            self::$keys['zset']['value']
        );
    }

    public function testSeeInRedisExistingZSetWithCorrectValueWithoutScores()
    {
        $this->shouldFail();
        $this->module->seeInRedis(
            self::$keys['zset']['name'],
            array_keys(self::$keys['zset']['value'])
        );
    }

    public function testSeeInRedisExistingZSetWithCorrectValueDifferentOrder()
    {
        $this->shouldFail();
        $this->module->seeInRedis(
            self::$keys['zset']['name'],
            array_reverse(self::$keys['zset']['value'])
        );
    }

    public function testSeeInRedisExistingZSetWithIncorrectValue()
    {
        $this->shouldFail();
        $this->module->seeInRedis(
            self::$keys['zset']['name'],
            ['incorrect' => 1, 'value' => 2]
        );
    }

    // *******************************
    // Test seeInRedis() with Hashes
    // *******************************

    public function testSeeInRedisExistingHashWithCorrectValue()
    {
        $this->module->seeInRedis(
            self::$keys['hash']['name'],
            self::$keys['hash']['value']
        );
    }

    public function testSeeInRedisExistingHashWithCorrectValueDifferentOrder()
    {
        $this->module->seeInRedis(
            self::$keys['hash']['name'],
            array_reverse(self::$keys['hash']['value'])
        );
    }

    public function testSeeInRedisExistingHashWithIncorrectValue()
    {
        $this->shouldFail();
        $this->module->seeInRedis(
            self::$keys['hash']['name'],
            ['incorrect' => 'value']
        );
    }

    // ****************************************
    // Test seeRedisKeyContains() with non existing keys
    // ****************************************

    public function testSeeRedisKeyContainsNonExistingKey()
    {
        $this->shouldFail('\Codeception\Exception\ModuleException');
        $this->module->seeRedisKeyContains('doesnotexist', 'doesnotexist');
    }

    // ****************************************
    // Test dontSeeRedisKeyContains() with array args
    // ****************************************

    public function testSeeRedisKeyContainsWithArrayArgs()
    {
        $this->shouldFail('\Codeception\Exception\ModuleException');
        $this->module->dontSeeRedisKeyContains(
            self::$keys['hash']['name'],
            self::$keys['hash']['value']
        );
    }

    // *******************************
    // Test seeRedisKeyContains() with Strings
    // *******************************

    public function testSeeRedisKeyContainsStringWithCorrectSubstring()
    {
        $this->module->seeRedisKeyContains(
            self::$keys['string']['name'],
            substr(self::$keys['string']['value'], 2, -2)
        );
    }

    public function testSeeRedisKeyContainsStringWithIncorrectValue()
    {
        $this->shouldFail();
        $this->module->seeRedisKeyContains(
            self::$keys['string']['name'],
            'incorrect string'
        );
    }

    // *******************************
    // Test seeRedisKeyContains() with Lists
    // *******************************

    public function testSeeRedisKeyContainsListWithCorrectItem()
    {
        $this->module->seeRedisKeyContains(
            self::$keys['list']['name'],
            self::$keys['list']['value'][1]
        );
    }

    public function testSeeRedisKeyContainsListWithIncorrectItem()
    {
        $this->shouldFail();
        $this->module->seeRedisKeyContains(
            self::$keys['list']['name'],
            'incorrect'
        );
    }

    // *******************************
    // Test seeRedisKeyContains() with Sets
    // *******************************

    public function testSeeRedisKeyContainsSetWithCorrectItem()
    {
        $this->module->seeRedisKeyContains(
            self::$keys['set']['name'],
            self::$keys['set']['value'][1]
        );
    }

    public function testSeeRedisKeyContainsSetWithIncorrectItem()
    {
        $this->shouldFail();
        $this->module->seeRedisKeyContains(
            self::$keys['set']['name'],
            'incorrect'
        );
    }

    // *******************************
    // Test seeRedisKeyContains() with Sorted sets
    // *******************************

    public function testSeeRedisKeyContainsZSetWithCorrectItemWithScore()
    {
        $firstItem = array_slice(self::$keys['zset']['value'], 0, 1);
        $firstKey = key($firstItem);
        $this->module->seeRedisKeyContains(
            self::$keys['zset']['name'],
            $firstKey,
            $firstItem[$firstKey]
        );
    }

    public function testSeeRedisKeyContainsZSetWithCorrectItemWithIncorrectScore()
    {
        $this->shouldFail();
        $firstItem = array_slice(self::$keys['zset']['value'], 0, 1);
        $firstKey = key($firstItem);
        $this->module->seeRedisKeyContains(
            self::$keys['zset']['name'],
            $firstKey,
            'incorrect'
        );
    }

    public function testSeeRedisKeyContainsZSetWithCorrectItemWithoutScore()
    {
        $arrayKeys = array_keys(self::$keys['zset']['value']);
        $this->module->seeRedisKeyContains(
            self::$keys['zset']['name'],
            $arrayKeys[0]
        );
    }

    public function testSeeRedisKeyContainsZSetWithIncorrectItemWithoutScore()
    {
        $this->shouldFail();
        $this->module->seeRedisKeyContains(
            self::$keys['zset']['name'],
            'incorrect'
        );
    }

    public function testSeeRedisKeyContainsZSetWithIncorrectItemWithScore()
    {
        $this->shouldFail();
        $this->module->seeRedisKeyContains(
            self::$keys['zset']['name'],
            'incorrect',
            34
        );
    }

    // *******************************
    // Test seeRedisKeyContains() with Hashes
    // *******************************

    public function testSeeRedisKeyContainsHashWithCorrectFieldWithValue()
    {
        $firstField = array_slice(self::$keys['hash']['value'], 0, 1);
        $firstKey = key($firstField);
        $this->module->seeRedisKeyContains(
            self::$keys['hash']['name'],
            $firstKey,
            $firstField[$firstKey]
        );
    }

    public function testSeeRedisKeyContainsHashWithCorrectFieldWithIncorrectValue()
    {
        $this->shouldFail();
        $firstField = array_slice(self::$keys['hash']['value'], 0, 1);
        $firstKey = key($firstField);
        $this->module->seeRedisKeyContains(
            self::$keys['hash']['name'],
            $firstKey,
            'incorrect'
        );
    }

    public function testSeeRedisKeyContainsHashWithCorrectFieldWithoutValue()
    {
        $arrayKeys = array_keys(self::$keys['hash']['value']);
        $this->module->seeRedisKeyContains(
            self::$keys['hash']['name'],
            $arrayKeys[0]
        );
    }

    public function testSeeRedisKeyContainsHashWithIncorrectFieldWithoutValue()
    {
        $this->shouldFail();
        $this->module->seeRedisKeyContains(
            self::$keys['hash']['name'],
            'incorrect'
        );
    }

    public function testSeeRedisKeyContainsHashWithIncorrectFieldWithValue()
    {
        $this->shouldFail();
        $this->module->seeRedisKeyContains(
            self::$keys['hash']['name'],
            'incorrect',
            34
        );
    }

    // *******************************
    // Test sendCommandToRedis()
    // *******************************

    public function testSendCommandToRedis()
    {
        $this->module->sendCommandToRedis('hmset', 'myhash', 'f1', 4, 'f2', 'foo');
        $this->module->sendCommandToRedis('hincrby', 'myhash', 'f1', 8);
        $this->module->sendCommandToRedis('hDel', 'myhash', 'f2');

        $result = $this->module->sendCommandToRedis('hGetAll', 'myhash');

        $this->assertEquals(
            ['f1' => 12],
            $result
        );
    }

    // *******************************
    // Helper methods
    // *******************************

    /**
     * Explicitely cast the scores of a Zset associative array as float/double
     *
     * @param array $arr The ZSet associative array
     *
     * @return array
     */
    private function scoresToFloat(array $arr)
    {
        foreach ($arr as $member => $score) {
            $arr[$member] = (float) $score;
        }

        return $arr;
    }

    /**
     * Converts boolean values to "0" and "1"
     *
     * @param mixed $var The variable
     *
     * @return mixed
     */
    private function boolToString($var)
    {
        $copy = is_array($var) ? $var : [$var];

        foreach ($copy as $key => $value) {
            if (is_bool($value)) {
                $copy[$key] = $value ? '1' : '0';
            }
        }

        return is_array($var) ? $copy : $copy[0];
    }
}
