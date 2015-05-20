<?php
class AssertsTest extends PHPUnit_Framework_TestCase {

    public function testAsserts()
    {
        $module = new \Codeception\Module\Asserts;
        $module->assertEquals(1,1);
        $module->assertContains(1,[1,2]);
        $module->assertSame(1,1);
        $module->assertNotSame(1,true);
    }

}
