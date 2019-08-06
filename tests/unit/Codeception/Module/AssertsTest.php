<?php
class AssertsTest extends \Codeception\PHPUnit\TestCase
{
    /** @var \Codeception\Module\Asserts */
    protected $module;

    public function _setUp()
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
        //assertInternalType is deprecated and will be removed in PHPUnit 9
        //$this->module->assertInternalType('integer', 5);
        $this->module->assertArrayHasKey('one', ['one' => 1, 'two' => 2]);
        //assertArraySubset is deprecated and will be removed in PHPUnit 9
        //$this->module->assertArraySubset(['foo' => [1]], ['foo' => [1, 2]]);
        $this->module->assertCount(3, [1, 2, 3]);

        $this->module->assertStringContainsString('bar', 'foobar');
        $this->module->assertStringContainsStringignoringCase('bar', 'FooBar');
        $this->module->assertStringNotContainsString('baz', 'foobar');
        $this->module->assertStringNotContainsStringignoringCase('baz', 'FooBar');

        $this->module->assertIsArray([1, 2, 3]);
        $this->module->assertIsBool(true);
        $this->module->assertIsFloat(1.2);
        $this->module->assertIsInt(2);
        $this->module->assertIsNumeric('12.34');
        $this->module->assertIsObject(new stdClass());
        $this->module->assertIsResource(fopen(__FILE__, 'r'));
        $this->module->assertIsString('test');
        $this->module->assertIsScalar('test');
        $this->module->assertIsCallable(function() {});

        $this->module->assertIsNotArray(false);
        $this->module->assertIsNotBool([1, 2, 3]);
        $this->module->assertIsNotFloat(false);
        $this->module->assertIsNotInt(false);
        $this->module->assertIsNotNumeric(false);
        $this->module->assertIsNotObject(false);
        $this->module->assertIsNotResource(false);
        $this->module->assertIsNotString(false);
        $this->module->assertIsNotScalar(function() {});
        $this->module->assertIsNotCallable('test');
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

    public function testExceptionFails()
    {
        $this->expectException(PHPUnit\Framework\AssertionFailedError::class);

        $this->module->expectException(new Exception('here', 200), function () {
            throw new Exception('here', 2);
        });
    }

    public function testOutputExceptionTimeWhenNothingCaught()
    {
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessageRegExp('/RuntimeException/');

        $this->module->expectException(RuntimeException::class, function () {
        });
    }

    public function testExpectThrowable()
    {
        $this->module->expectThrowable('Exception', function () {
            throw new Exception();
        });
        $this->module->expectThrowable(new Exception('here'), function () {
            throw new Exception('here');
        });
        $this->module->expectThrowable(new Exception('here', 200), function () {
            throw new Exception('here', 200);
        });
    }

    public function testExpectThrowableFailOnDifferentClass()
    {
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

        $this->module->expectThrowable(new RuntimeException(), function () {
            throw new Exception();
        });
    }

    public function testExpectThrowableFailOnDifferentMessage()
    {
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

        $this->module->expectThrowable(new Exception('foo', 200), function () {
            throw new Exception('bar', 200);
        });
    }

    public function testExpectThrowableFailOnDifferentCode()
    {
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

        $this->module->expectThrowable(new Exception('foobar', 200), function () {
            throw new Exception('foobar', 2);
        });
    }

    public function testExpectThrowableFailOnNothingCaught()
    {
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessageRegExp('/RuntimeException/');

        $this->module->expectThrowable(RuntimeException::class, function () {
        });
    }
}
