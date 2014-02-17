<?php
class AssertsTest extends PHPUnit_Framework_TestCase {

    public function testAsserts()
    {
        $module = new \Codeception\Module\Asserts;
        $module->seeEquals(1,1);
        $module->seeContains(1,[1,2]);
    }

}
 