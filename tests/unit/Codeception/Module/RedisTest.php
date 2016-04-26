<?php

use Codeception\Lib\ModuleContainer;
use Codeception\Module\Redis;

class RedisTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected static $config = array(
        'database' => 15
    );

    /**
     * @var Redis
     */
    protected static $module;

    /**
     * Keys that will be created for the tests
     *
     * @var array
     */
    protected static $keys = array(
        'string' => array(
            'name' => 'test:string',
            'value' => 'hello'
        ),
        'list' => array(
            'name' => 'test:list',
            'value' => array('riri', 'fifi', 'loulou')
        ),
        'set' => array(
            'name' => 'test:set',
            'value' => array('huey', 'dewey', 'louie')
        ),
        'zset' => array(
            'name' => 'test:zset',
            'value' => array('juanito' => 1, 'jorgito' => 2, 'jaimito' => 3)
        ),
        'hash' => array(
            'name' => 'test:hash',
            'value' => array('Tick' => true, 'Trick' => 'dewey', 'Track' => 42)
        )
    );


    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        /** @var ModuleContainer $container */
        $container = make_container();
        self::$module = new Redis($container);
        self::$module->_setConfig(self::$config);
        self::$module->_initialize();
    }

    /**
     * {@inheritdoc}
     *
     * Every time a test starts, cleanup the database and populates it with some
     * dummy data.
     */
    protected function setUp()
    {
        self::$module->driver->flushDb();

        $addMethods = array(
            'string' => 'set',
            'list' => 'rPush',
            'set' => 'sAdd',
            'zset' => 'zAdd',
            'hash' => 'hMSet'
        );
        foreach (self::$keys as $type => $key) {
            $value = $key['value'];

            // Remove this when CRedis implements zAdd() with associative arrays
            if ($type === 'zset') {
                $value = array();
                foreach ($key['value'] as $member => $score) {
                    $value[] = $score;
                    $value[] = $member;
                }
            }

            self::$module->driver->{$addMethods[$type]}(
                $key['name'],
                $value
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
        self::$module->grabFromRedis('doesnotexist');
    }

    // *******************************
    // Test grabFromRedis() with Strings
    // *******************************

    public function testGrabFromRedisString()
    {
        $result = self::$module->grabFromRedis(self::$keys['string']['name']);
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
        $result = self::$module->grabFromRedis(
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
        $result = self::$module->grabFromRedis(
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
        $result = self::$module->grabFromRedis(
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
        self::$module->grabFromRedis(
            self::$keys['zset']['name'],
            1
        );
    }

    public function testGrabFromRedisZSetAll()
    {
        $reference = $this->scoresToFloat(self::$keys['zset']['value']);
        $result = self::$module->grabFromRedis(self::$keys['zset']['name']);

        $this->assertSame($reference, $result);
    }

    public function testGrabFromRedisZSetRange()
    {
        $rangeFrom = 1;
        $rangeTo = 2;
        $result = self::$module->grabFromRedis(
            self::$keys['zset']['name'],
            $rangeFrom,
            $rangeTo
        );
        $this->assertSame(
            $this->scoresToFloat(array_slice(
                self::$keys['zset']['value'],
                $rangeFrom,
                ($rangeTo - $rangeFrom + 1)
            )),
            $result
        );
    }

    // *******************************
    // Test grabFromRedis() with Hashes
    // *******************************

    public function testGrabFromRedisHashAll()
    {
        $result = self::$module->grabFromRedis(
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

        $result = self::$module->grabFromRedis(
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
        $newKey = array(
            'name' => 'test:string-create',
            'value' => 'testing string creation'
        );
        self::$module->haveInRedis(
            'string',
            $newKey['name'],
            $newKey['value']
        );
        $this->assertSame(
            $newKey['value'],
            self::$module->driver->get($newKey['name'])
        );
    }

    public function testHaveInRedisExistingString()
    {
        $newValue = 'new value';
        self::$module->haveInRedis(
            'string',
            self::$keys['string']['name'],
            $newValue
        );
        $this->assertSame(
            $newValue,
            self::$module->driver->get(self::$keys['string']['name'])
        );
    }

    // *******************************
    // Test haveInRedis() with Lists
    // *******************************

    public function testHaveInRedisNonExistingListArrayArg()
    {
        $newKey = array(
            'name' => 'test:list-create-array',
            'value' => array('testing', 'list', 'creation')
        );
        self::$module->haveInRedis(
            'list',
            $newKey['name'],
            $newKey['value']
        );
        $this->assertSame(
            $newKey['value'],
            self::$module->driver->lrange($newKey['name'], 0, -1)
        );
    }

    public function testHaveInRedisNonExistingListScalarArg()
    {
        $newKey = array(
            'name' => 'test:list-create-scalar',
            'value' => 'testing list creation'
        );
        self::$module->haveInRedis(
            'list',
            $newKey['name'],
            $newKey['value']
        );
        $this->assertSame(
            array($newKey['value']),
            self::$module->driver->lrange($newKey['name'], 0, -1)
        );
    }

    public function testHaveInRedisExistingListArrayArg()
    {
        $newValue = array('testing', 'list', 'creation');

        self::$module->haveInRedis(
            'list',
            self::$keys['list']['name'],
            $newValue
        );
        $this->assertSame(
            array_merge(
                self::$keys['list']['value'],
                $newValue
            ),
            self::$module->driver->lrange(self::$keys['list']['name'], 0, -1)
        );
    }

    public function testHaveInRedisExistingListArrayScalar()
    {
        $newValue = 'testing list creation';

        self::$module->haveInRedis(
            'list',
            self::$keys['list']['name'],
            $newValue
        );
        $this->assertSame(
            array_merge(
                self::$keys['list']['value'],
                array($newValue)
            ),
            self::$module->driver->lrange(self::$keys['list']['name'], 0, -1)
        );
    }

    // *******************************
    // Test haveInRedis() with Sets
    // *******************************

    public function testHaveInRedisNonExistingSetArrayArg()
    {
        $newKey = array(
            'name' => 'test:set-create-array',
            'value' => array('testing', 'set', 'creation')
        );
        self::$module->haveInRedis(
            'set',
            $newKey['name'],
            $newKey['value']
        );

        $expected = $newKey['value'];
        sort($expected);

        $result = self::$module->driver->sMembers($newKey['name']);
        sort($result);

        $this->assertSame($expected, $result);
    }

    public function testHaveInRedisNonExistingSetScalarArg()
    {
        $newKey = array(
            'name' => 'test:set-create-scalar',
            'value' => 'testing set creation'
        );
        self::$module->haveInRedis(
            'set',
            $newKey['name'],
            $newKey['value']
        );
        $this->assertSame(
            array($newKey['value']),
            self::$module->driver->sMembers($newKey['name'])
        );
    }

    public function testHaveInRedisExistingSetArrayArg()
    {
        $newValue = array('testing', 'set', 'creation');

        self::$module->haveInRedis(
            'set',
            self::$keys['set']['name'],
            $newValue
        );
        $expectedValue = array_merge(
            self::$keys['set']['value'],
            $newValue
        );
        sort($expectedValue);

        $result = self::$module->driver->sMembers(self::$keys['set']['name']);
        sort($result);

        $this->assertSame($expectedValue, $result);
    }

    public function testHaveInRedisExistingSetArrayScalar()
    {
        $newValue = 'testing set creation';

        self::$module->haveInRedis(
            'set',
            self::$keys['set']['name'],
            $newValue
        );

        $expectedResult = array_merge(
            self::$keys['set']['value'],
            array($newValue)
        );
        sort($expectedResult);

        $result = self::$module->driver->sMembers(self::$keys['set']['name']);
        sort($result);

        $this->assertSame($expectedResult, $result);
    }

    // *******************************
    // Test haveInRedis() with Sorted sets
    // *******************************

    public function testHaveInRedisZSetScalar()
    {
        $this->shouldFail('\Codeception\Exception\ModuleException');
        self::$module->haveInRedis(
            'zset',
            'test:zset-create-array',
            'foobar'
        );
    }

    public function testHaveInRedisNonExistingZSetArrayArg()
    {
        $newKey = array(
            'name' => 'test:zset-create-array',
            'value' => array(
                'testing' => 2,
                'zset' => 1,
                'creation' => 2,
                'foo' => 3
            )
        );
        self::$module->haveInRedis(
            'zset',
            $newKey['name'],
            $newKey['value']
        );
        $this->assertSame(
            array('zset' => 1.0, 'creation' => 2.0, 'testing' => 2.0, 'foo' => 3.0),
            self::$module->driver->zrange($newKey['name'], 0, -1, true)
        );
    }

    public function testHaveInRedisExistingZSetArrayArg()
    {
        $newValue = array(
            'testing' => 2,
            'zset' => 1,
            'creation' => 2,
            'foo' => 3
        );

        self::$module->haveInRedis(
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
        $expected = $this->scoresToFloat($expected);

        $this->assertSame(
            $expected,
            self::$module->driver->zRange(self::$keys['zset']['name'], 0, -1, true)
        );
    }

    // *******************************
    // Test haveInRedis() with Hashes
    // *******************************

    public function testHaveInRedisHashScalar()
    {
        $this->shouldFail('\Codeception\Exception\ModuleException');
        self::$module->haveInRedis(
            'hash',
            'test:hash-create-array',
            'foobar'
        );
    }

    public function testHaveInRedisNonExistingHashArrayArg()
    {
        $newKey = array(
            'name' => 'test:hash-create-array',
            'value' => array(
                'obladi' => 'oblada',
                'nope' => false,
                'zero' => 0
            )
        );
        self::$module->haveInRedis(
            'hash',
            $newKey['name'],
            $this->boolToString($newKey['value'])
        );
        $this->assertEquals(
            $this->boolToString($newKey['value']),
            self::$module->driver->hGetAll($newKey['name'])
        );
    }

    public function testHaveInRedisExistingHashArrayArg()
    {
        $newValue = array(
            'obladi' => 'oblada',
            'nope' => false,
            'zero' => 0
        );
        self::$module->haveInRedis(
            'hash',
            self::$keys['hash']['name'],
            $newValue
        );
        $this->assertEquals(
            array_merge(
                self::$keys['hash']['value'],
                $newValue
            ),
            self::$module->driver->hGetAll(self::$keys['hash']['name'])
        );
    }

    // ****************************************
    // Test dontSeeInRedis() with non existing keys
    // ****************************************

    public function testDontSeeInRedisNonExistingKeyWithoutValue()
    {
        self::$module->dontSeeInRedis('doesnotexist');
    }

    public function testDontSeeInRedisNonExistingKeyWithValue()
    {
        self::$module->dontSeeInRedis(
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
        self::$module->dontSeeInRedis(
            self::$keys['string']['name']
        );
    }

    // *******************************
    // Test dontSeeInRedis() with Strings
    // *******************************

    public function testDontSeeInRedisExistingStringWithCorrectValue()
    {
        $this->shouldFail();
        self::$module->dontSeeInRedis(
            self::$keys['string']['name'],
            self::$keys['string']['value']
        );
    }

    public function testDontSeeInRedisExistingStringWithIncorrectValue()
    {
        self::$module->dontSeeInRedis(
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
        self::$module->dontSeeInRedis(
            self::$keys['list']['name'],
            self::$keys['list']['value']
        );
    }

    public function testDontSeeInRedisExistingListWithCorrectValueDifferentOrder()
    {
        self::$module->dontSeeInRedis(
            self::$keys['list']['name'],
            array_reverse(self::$keys['list']['value'])
        );
    }

    public function testDontSeeInRedisExistingListWithIncorrectValue()
    {
        self::$module->dontSeeInRedis(
            self::$keys['list']['name'],
            array('incorrect', 'value')
        );
    }

    // *******************************
    // Test dontSeeInRedis() with Sets
    // *******************************

    public function testDontSeeInRedisExistingSetWithCorrectValue()
    {
        $this->shouldFail();
        self::$module->dontSeeInRedis(
            self::$keys['set']['name'],
            self::$keys['set']['value']
        );
    }

    public function testDontSeeInRedisExistingSetWithCorrectValueDifferentOrder()
    {
        $this->shouldFail();
        self::$module->dontSeeInRedis(
            self::$keys['set']['name'],
            array_reverse(self::$keys['set']['value'])
        );
    }

    public function testDontSeeInRedisExistingSetWithIncorrectValue()
    {
        self::$module->dontSeeInRedis(
            self::$keys['set']['name'],
            array('incorrect', 'value')
        );
    }

    // *******************************
    // Test dontSeeInRedis() with Sorted Sets
    // *******************************

    public function testDontSeeInRedisExistingZSetWithCorrectValue()
    {
        $this->shouldFail();
        self::$module->dontSeeInRedis(
            self::$keys['zset']['name'],
            self::$keys['zset']['value']
        );
    }

    public function testDontSeeInRedisExistingZSetWithCorrectValueWithoutScores()
    {
        self::$module->dontSeeInRedis(
            self::$keys['zset']['name'],
            array_keys(self::$keys['zset']['value'])
        );
    }

    public function testDontSeeInRedisExistingZSetWithCorrectValueDifferentOrder()
    {
        self::$module->dontSeeInRedis(
            self::$keys['zset']['name'],
            array_reverse(self::$keys['zset']['value'])
        );
    }

    public function testDontSeeInRedisExistingZSetWithIncorrectValue()
    {
        self::$module->dontSeeInRedis(
            self::$keys['zset']['name'],
            array('incorrect' => 1, 'value' => 2)
        );
    }

    // *******************************
    // Test dontSeeInRedis() with Hashes
    // *******************************

    public function testDontSeeInRedisExistingHashWithCorrectValue()
    {
        $this->shouldFail();
        self::$module->dontSeeInRedis(
            self::$keys['hash']['name'],
            self::$keys['hash']['value']
        );
    }

    public function testDontSeeInRedisExistingHashWithCorrectValueDifferentOrder()
    {
        $this->shouldFail();
        self::$module->dontSeeInRedis(
            self::$keys['hash']['name'],
            array_reverse(self::$keys['hash']['value'])
        );
    }

    public function testDontSeeInRedisExistingHashWithIncorrectValue()
    {
        self::$module->dontSeeInRedis(
            self::$keys['hash']['name'],
            array('incorrect' => 'value')
        );
    }

    // ****************************************
    // Test dontSeeRedisKeyContains() with non existing keys
    // ****************************************

    public function testDontSeeRedisKeyContainsNonExistingKey()
    {
        $this->shouldFail('\Codeception\Exception\ModuleException');
        self::$module->dontSeeRedisKeyContains('doesnotexist', 'doesnotexist');
    }

    // ****************************************
    // Test dontSeeRedisKeyContains() with array args
    // ****************************************

    public function testDontSeeRedisKeyContainsWithArrayArgs()
    {
        $this->shouldFail('\Codeception\Exception\ModuleException');
        self::$module->dontSeeRedisKeyContains(
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
        self::$module->dontSeeRedisKeyContains(
            self::$keys['string']['name'],
            substr(self::$keys['string']['value'], 2, -2)
        );
    }

    public function testDontSeeRedisKeyContainsStringWithIncorrectValue()
    {
        self::$module->dontSeeRedisKeyContains(
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
        self::$module->dontSeeRedisKeyContains(
            self::$keys['list']['name'],
            self::$keys['list']['value'][1]
        );
    }

    public function testDontSeeRedisKeyContainsListWithIncorrectItem()
    {
        self::$module->dontSeeRedisKeyContains(
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
        self::$module->dontSeeRedisKeyContains(
            self::$keys['set']['name'],
            self::$keys['set']['value'][1]
        );
    }

    public function testDontSeeRedisKeyContainsSetWithIncorrectItem()
    {
        self::$module->dontSeeRedisKeyContains(
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
        self::$module->dontSeeRedisKeyContains(
            self::$keys['zset']['name'],
            $firstMember,
            $firstItem[$firstMember]
        );
    }

    public function testDontSeeRedisKeyContainsZSetWithCorrectItemWithIncorrectScore()
    {
        $firstItem = array_slice(self::$keys['zset']['value'], 0, 1);
        $firstKey = key($firstItem);
        self::$module->dontSeeRedisKeyContains(
            self::$keys['zset']['name'],
            $firstKey,
            'incorrect'
        );
    }

    public function testDontSeeRedisKeyContainsZSetWithCorrectItemWithoutScore()
    {
        $this->shouldFail();
        $arrayKeys = array_keys(self::$keys['zset']['value']);
        self::$module->dontSeeRedisKeyContains(
            self::$keys['zset']['name'],
            $arrayKeys[0]
        );
    }

    public function testDontSeeRedisKeyContainsZSetWithIncorrectItemWithoutScore()
    {
        self::$module->dontSeeRedisKeyContains(
            self::$keys['zset']['name'],
            'incorrect'
        );
    }

    public function testDontSeeRedisKeyContainsZSetWithIncorrectItemWithScore()
    {
        self::$module->dontSeeRedisKeyContains(
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
        self::$module->dontSeeRedisKeyContains(
            self::$keys['hash']['name'],
            $firstKey,
            $firstField[$firstKey]
        );
    }

    public function testDontSeeRedisKeyContainsHashWithCorrectFieldWithIncorrectValue()
    {
        $firstField = array_slice(self::$keys['hash']['value'], 0, 1);
        $firstKey = key($firstField);
        self::$module->dontSeeRedisKeyContains(
            self::$keys['hash']['name'],
            $firstKey,
            'incorrect'
        );
    }

    public function testDontSeeRedisKeyContainsHashWithCorrectFieldWithoutValue()
    {
        $this->shouldFail();
        $arrayKeys = array_keys(self::$keys['hash']['value']);
        self::$module->dontSeeRedisKeyContains(
            self::$keys['hash']['name'],
            $arrayKeys[0]
        );
    }

    public function testDontSeeRedisKeyContainsHashWithIncorrectFieldWithoutValue()
    {
        self::$module->dontSeeRedisKeyContains(
            self::$keys['hash']['name'],
            'incorrect'
        );
    }

    public function testDontSeeRedisKeyContainsHashWithIncorrectFieldWithValue()
    {
        self::$module->dontSeeRedisKeyContains(
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
        self::$module->seeInRedis('doesnotexist');
    }

    public function testSeeInRedisNonExistingKeyWithValue()
    {
        $this->shouldFail();
        self::$module->seeInRedis(
            'doesnotexist',
            'some value'
        );
    }

    // *******************************
    // Test seeInRedis() without value
    // *******************************

    public function testSeeInRedisExistingKeyWithoutValue()
    {
        self::$module->seeInRedis(
            self::$keys['string']['name']
        );
    }

    // *******************************
    // Test seeInRedis() with Strings
    // *******************************

    public function testSeeInRedisExistingStringWithCorrectValue()
    {
        self::$module->seeInRedis(
            self::$keys['string']['name'],
            self::$keys['string']['value']
        );
    }

    public function testSeeInRedisExistingStringWithIncorrectValue()
    {
        $this->shouldFail();
        self::$module->seeInRedis(
            self::$keys['string']['name'],
            'incorrect value'
        );
    }

    // *******************************
    // Test seeInRedis() with Lists
    // *******************************

    public function testSeeInRedisExistingListWithCorrectValue()
    {
        self::$module->seeInRedis(
            self::$keys['list']['name'],
            self::$keys['list']['value']
        );
    }

    public function testSeeInRedisExistingListWithCorrectValueDifferentOrder()
    {
        $this->shouldFail();
        self::$module->seeInRedis(
            self::$keys['list']['name'],
            array_reverse(self::$keys['list']['value'])
        );
    }

    public function testSeeInRedisExistingListWithIncorrectValue()
    {
        $this->shouldFail();
        self::$module->seeInRedis(
            self::$keys['list']['name'],
            array('incorrect', 'value')
        );
    }

    // *******************************
    // Test seeInRedis() with Sets
    // *******************************

    public function testSeeInRedisExistingSetWithCorrectValue()
    {
        self::$module->seeInRedis(
            self::$keys['set']['name'],
            self::$keys['set']['value']
        );
    }

    public function testSeeInRedisExistingSetWithCorrectValueDifferentOrder()
    {
        self::$module->seeInRedis(
            self::$keys['set']['name'],
            array_reverse(self::$keys['set']['value'])
        );
    }

    public function testSeeInRedisExistingSetWithIncorrectValue()
    {
        $this->shouldFail();
        self::$module->seeInRedis(
            self::$keys['set']['name'],
            array('incorrect', 'value')
        );
    }

    // *******************************
    // Test seeInRedis() with Sorted Sets
    // *******************************

    public function testSeeInRedisExistingZSetWithCorrectValue()
    {
        self::$module->seeInRedis(
            self::$keys['zset']['name'],
            self::$keys['zset']['value']
        );
    }

    public function testSeeInRedisExistingZSetWithCorrectValueWithoutScores()
    {
        $this->shouldFail();
        self::$module->seeInRedis(
            self::$keys['zset']['name'],
            array_keys(self::$keys['zset']['value'])
        );
    }

    public function testSeeInRedisExistingZSetWithCorrectValueDifferentOrder()
    {
        $this->shouldFail();
        self::$module->seeInRedis(
            self::$keys['zset']['name'],
            array_reverse(self::$keys['zset']['value'])
        );
    }

    public function testSeeInRedisExistingZSetWithIncorrectValue()
    {
        $this->shouldFail();
        self::$module->seeInRedis(
            self::$keys['zset']['name'],
            array('incorrect' => 1, 'value' => 2)
        );
    }

    // *******************************
    // Test seeInRedis() with Hashes
    // *******************************

    public function testSeeInRedisExistingHashWithCorrectValue()
    {
        self::$module->seeInRedis(
            self::$keys['hash']['name'],
            self::$keys['hash']['value']
        );
    }

    public function testSeeInRedisExistingHashWithCorrectValueDifferentOrder()
    {
        self::$module->seeInRedis(
            self::$keys['hash']['name'],
            array_reverse(self::$keys['hash']['value'])
        );
    }

    public function testSeeInRedisExistingHashWithIncorrectValue()
    {
        $this->shouldFail();
        self::$module->seeInRedis(
            self::$keys['hash']['name'],
            array('incorrect' => 'value')
        );
    }

    // ****************************************
    // Test seeRedisKeyContains() with non existing keys
    // ****************************************

    public function testSeeRedisKeyContainsNonExistingKey()
    {
        $this->shouldFail('\Codeception\Exception\ModuleException');
        self::$module->seeRedisKeyContains('doesnotexist', 'doesnotexist');
    }

    // ****************************************
    // Test dontSeeRedisKeyContains() with array args
    // ****************************************

    public function testSeeRedisKeyContainsWithArrayArgs()
    {
        $this->shouldFail('\Codeception\Exception\ModuleException');
        self::$module->dontSeeRedisKeyContains(
            self::$keys['hash']['name'],
            self::$keys['hash']['value']
        );
    }

    // *******************************
    // Test seeRedisKeyContains() with Strings
    // *******************************

    public function testSeeRedisKeyContainsStringWithCorrectSubstring()
    {
        self::$module->seeRedisKeyContains(
            self::$keys['string']['name'],
            substr(self::$keys['string']['value'], 2, -2)
        );
    }

    public function testSeeRedisKeyContainsStringWithIncorrectValue()
    {
        $this->shouldFail();
        self::$module->seeRedisKeyContains(
            self::$keys['string']['name'],
            'incorrect string'
        );
    }

    // *******************************
    // Test seeRedisKeyContains() with Lists
    // *******************************

    public function testSeeRedisKeyContainsListWithCorrectItem()
    {
        self::$module->seeRedisKeyContains(
            self::$keys['list']['name'],
            self::$keys['list']['value'][1]
        );
    }

    public function testSeeRedisKeyContainsListWithIncorrectItem()
    {
        $this->shouldFail();
        self::$module->seeRedisKeyContains(
            self::$keys['list']['name'],
            'incorrect'
        );
    }

    // *******************************
    // Test seeRedisKeyContains() with Sets
    // *******************************

    public function testSeeRedisKeyContainsSetWithCorrectItem()
    {
        self::$module->seeRedisKeyContains(
            self::$keys['set']['name'],
            self::$keys['set']['value'][1]
        );
    }

    public function testSeeRedisKeyContainsSetWithIncorrectItem()
    {
        $this->shouldFail();
        self::$module->seeRedisKeyContains(
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
        self::$module->seeRedisKeyContains(
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
        self::$module->seeRedisKeyContains(
            self::$keys['zset']['name'],
            $firstKey,
            'incorrect'
        );
    }

    public function testSeeRedisKeyContainsZSetWithCorrectItemWithoutScore()
    {
        $arrayKeys = array_keys(self::$keys['zset']['value']);
        self::$module->seeRedisKeyContains(
            self::$keys['zset']['name'],
            $arrayKeys[0]
        );
    }

    public function testSeeRedisKeyContainsZSetWithIncorrectItemWithoutScore()
    {
        $this->shouldFail();
        self::$module->seeRedisKeyContains(
            self::$keys['zset']['name'],
            'incorrect'
        );
    }

    public function testSeeRedisKeyContainsZSetWithIncorrectItemWithScore()
    {
        $this->shouldFail();
        self::$module->seeRedisKeyContains(
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
        self::$module->seeRedisKeyContains(
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
        self::$module->seeRedisKeyContains(
            self::$keys['hash']['name'],
            $firstKey,
            'incorrect'
        );
    }

    public function testSeeRedisKeyContainsHashWithCorrectFieldWithoutValue()
    {
        $arrayKeys = array_keys(self::$keys['hash']['value']);
        self::$module->seeRedisKeyContains(
            self::$keys['hash']['name'],
            $arrayKeys[0]
        );
    }

    public function testSeeRedisKeyContainsHashWithIncorrectFieldWithoutValue()
    {
        $this->shouldFail();
        self::$module->seeRedisKeyContains(
            self::$keys['hash']['name'],
            'incorrect'
        );
    }

    public function testSeeRedisKeyContainsHashWithIncorrectFieldWithValue()
    {
        $this->shouldFail();
        self::$module->seeRedisKeyContains(
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
        self::$module->sendCommandToRedis(
            'hmset', 'myhash', array('field1' => 4, 'field2' => 'foobar')
        );
        self::$module->sendCommandToRedis(
            'hIncrBy',
            array('myhash', 'field1', 8)
        );
        self::$module->sendCommandToRedis(
            'hDel',
            array('myhash', 'field2')
        );

        $result = self::$module->sendCommandToRedis(
            'hGetAll',
            array('myhash')
        );

        $this->assertEquals(
            array('field1' => 12),
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
        $copy = is_array($var) ? $var : array($var);

        foreach ($copy as $key => $value) {
            if (is_bool($value)) {
                $copy[$key] = $value ? '1' : '0';
            }
        }

        return is_array($var) ? $copy : $copy[0];
    }
}
