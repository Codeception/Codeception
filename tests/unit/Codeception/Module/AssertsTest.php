<?php
class AssertsTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Codeception\Module\Asserts */
    protected $module;

    public function setUp()
    {
        $this->module = new \Codeception\Module\Asserts(make_container());
    }

    public function testAsserts()
    {
        $this->module->assertEquals(1, 1);
        $this->module->assertContains(1, [1, 2]);
        $this->module->assertSame(1, 1);
        $this->module->assertNotSame(1, '1');
        $this->module->assertRegExp('/^[\d]$/', '1');
        $this->module->assertNotRegExp('/^[a-z]$/', '1');
        $this->module->assertStringStartsWith('fo', 'foo');
        $this->module->assertStringStartsNotWith('ba', 'foo');
        $this->module->assertEmpty([]);
        $this->module->assertNotEmpty([1]);
        $this->module->assertNull(null);
        $this->module->assertNotNull('');
        $this->module->assertNotNull(false);
        $this->module->assertNotNull(0);
        $this->module->assertTrue(true);
        $this->module->assertNotTrue(false);
        $this->module->assertNotTrue(null);
        $this->module->assertNotTrue('foo');
        $this->module->assertFalse(false);
        $this->module->assertNotFalse(true);
        $this->module->assertNotFalse(null);
        $this->module->assertNotFalse('foo');
        $this->module->assertFileExists(__FILE__);
        $this->module->assertFileNotExists(__FILE__ . '.notExist');
        $this->module->assertInstanceOf('Exception', new Exception());
        $this->module->assertInternalType('integer', 5);
        $this->module->assertArrayHasKey('one', ['one' => 1, 'two' => 2]);
        $this->module->assertArraySubset(['foo' => [1]], ['foo' => [1, 2]]);
        $this->module->assertCount(3, [1, 2, 3]);
    }

    public function testExceptions()
    {
        $this->module->expectException('Exception', function () {
            throw new Exception;
        });
        $this->module->expectException(new Exception('here'), function () {
            throw new Exception('here');
        });
        $this->module->expectException(new Exception('here', 200), function () {
            throw new Exception('here', 200);
        });
    }

    /**
     * @expectedException PHPUnit\Framework\AssertionFailedError
     */
    public function testExceptionFails()
    {
        $this->module->expectException(new Exception('here', 200), function () {
            throw new Exception('here', 2);
        });
    }

    /**
     * @expectedException PHPUnit\Framework\AssertionFailedError
     * @expectedExceptionMessageRegExp /RuntimeException/
     */
    public function testOutputExceptionTimeWhenNothingCaught()
    {
        $this->module->expectException(RuntimeException::class, function () {
        });
    }

    public function testExpectThrowable()
    {
        $this->module->expectThrowable('Exception', function () {
            throw new Exception();
        });
        $this->module->expectThrowable(new Error('here'), function () {
            throw new Error('here');
        });
        $this->module->expectThrowable(new Exception('here', 200), function () {
            throw new Exception('here', 200);
        });
    }

    /**
     * @expectedException PHPUnit\Framework\AssertionFailedError
     */
    public function testExpectThrowableFailOnDifferentClass()
    {
        $this->module->expectThrowable(new Exception(), function () {
            throw new Error();
        });
    }

    /**
     * @expectedException PHPUnit\Framework\AssertionFailedError
     */
    public function testExpectThrowableFailOnDifferentMessage()
    {
        $this->module->expectThrowable(new Exception('foo', 200), function () {
            throw new Exception('bar', 200);
        });
    }

    /**
     * @expectedException PHPUnit\Framework\AssertionFailedError
     */
    public function testExpectThrowableFailOnDifferentCode()
    {
        $this->module->expectThrowable(new Exception('foobar', 200), function () {
            throw new Exception('foobar', 2);
        });
    }

    /**
     * @expectedException PHPUnit\Framework\AssertionFailedError
     * @expectedExceptionMessageRegExp /RuntimeException/
     */
    public function testExpectThrowableFailOnNothingCaught()
    {
        $this->module->expectThrowable(RuntimeException::class, function () {
        });
    }
}
