<?php
class AssertsTest extends PHPUnit_Framework_TestCase
{
    public function testAsserts()
    {
        $module = new \Codeception\Module\Asserts(make_container());
        $module->assertEquals(1,1);
        $module->assertContains(1,[1,2]);
    }

}
 