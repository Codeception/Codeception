<?php

if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
    class php55Test extends PHPUnit_Framework_TestCase
    {
        public function testOfTest() {
            $this->assertEquals('PHPUnit_Framework_TestCase', PHPUnit_Framework_TestCase::class);
        }

    }
}
