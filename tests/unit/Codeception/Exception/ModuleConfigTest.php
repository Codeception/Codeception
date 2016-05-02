<?php

class ModuleConfigTest extends \PHPUnit_Framework_TestCase
{
    // tests
    public function testCanBeCreatedForModuleName()
    {
        $exception = new \Codeception\Exception\ModuleConfigException('Codeception\Module\WebDriver', "Hello world");
        $this->assertEquals("WebDriver module is not configured!\n \nHello world", $exception->getMessage());
    }

    public function testCanBeCreatedForModuleObject()
    {
        $exception = new \Codeception\Exception\ModuleConfigException(
            new \Codeception\Module\CodeHelper(make_container()),
            "Hello world"
        );
        $this->assertEquals("CodeHelper module is not configured!\n \nHello world", $exception->getMessage());
    }
}
