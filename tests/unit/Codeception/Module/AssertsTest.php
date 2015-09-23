<?php
class AssertsTest extends PHPUnit_Framework_TestCase
{
    public function testAsserts()
    {
        $module = new \Codeception\Module\Asserts(make_container());
        $module->assertEquals(1,1);
        $module->assertContains(1,[1,2]);
        $module->assertSame(1,1);
        $module->assertNotSame(1,true);
        $module->assertRegExp('/^[\d]$/','1');
        $module->assertNotRegExp('/^[a-z]$/','1');
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
    }

}
