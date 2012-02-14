<?php

class CodeceptTest extends \PHPUnit_Framework_TestCase
{
    
    public function testLatestVersion() {
        $this->markTestSkipped();
        $this->assertEquals(\Codeception\Codecept::VERSION,\Codeception\Codecept::checkLastVersion());
    }

}
