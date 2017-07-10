<?php
class AssertsTest extends PHPUnit_Framework_TestCase
{
    public function testAsserts()
    {
        $module = new \Codeception\Module\Asserts(make_container());
        $module->assertEquals(1, 1);
        $module->assertContains(1, [1, 2]);
        $module->assertSame(1, 1);
        $module->assertNotSame(1, '1');
        $module->assertRegExp('/^[\d]$/', '1');
        $module->assertNotRegExp('/^[a-z]$/', '1');
        $module->assertStringStartsWith('fo', 'foo');
        $module->assertStringStartsNotWith('ba', 'foo');
        $module->assertEmpty([]);
        $module->assertNotEmpty([1]);
        $module->assertNull(null);
        $module->assertNotNull('');
        $module->assertNotNull(false);
        $module->assertNotNull(0);
        $module->assertTrue(true);
        $module->assertFalse(false);
        $module->assertFileExists(__FILE__);
        $module->assertFileNotExists(__FILE__ . '.notExist');
        $module->assertInstanceOf('Exception', new Exception());
        $module->assertInternalType('integer', 5);
        $module->assertArrayHasKey('one', ['one' => 1, 'two' => 2]);
        $module->assertArraySubset(['foo' => [1]], ['foo' => [1, 2]]);
        $module->assertCount(3, [1, 2, 3]);
    }

    public function testExceptions()
    {
        $module = new \Codeception\Module\Asserts(make_container());
        $module->expectException('Exception', function () {
            throw new Exception;
        });
        $module->expectException(new Exception('here'), function () {
            throw new Exception('here');
        });
        $module->expectException(new Exception('here', 200), function () {
            throw new Exception('here', 200);
        });
    }

    /**
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testExceptionFails()
    {
        $module = new \Codeception\Module\Asserts(make_container());
        $module->expectException(new Exception('here', 200), function () {
            throw new Exception('here', 2);
        });
    }
}
