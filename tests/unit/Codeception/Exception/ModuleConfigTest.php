<?php

declare(strict_types=1);

class ModuleConfigTest extends \PHPUnit\Framework\TestCase
{
    // tests
    public function testCanBeCreatedForModuleName()
    {
        $exception = new \Codeception\Exception\ModuleConfigException('Codeception\Module\WebDriver', "Hello world");
        $this->assertSame("WebDriver module is not configured!\n \nHello world", $exception->getMessage());
    }

    public function testCanBeCreatedForModuleObject()
    {
        $exception = new \Codeception\Exception\ModuleConfigException(
            new \Codeception\Module\CodeHelper(make_container()),
            "Hello world"
        );
        $this->assertSame("CodeHelper module is not configured!\n \nHello world", $exception->getMessage());
    }
}
