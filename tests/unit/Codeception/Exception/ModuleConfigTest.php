<?php

declare(strict_types=1);

class ModuleConfigTest extends \PHPUnit\Framework\TestCase
{
    // tests
    public function testCanBeCreatedForModuleName(): void
    {
        $exception = new \Codeception\Exception\ModuleConfigException('Codeception\Module\WebDriver', "Hello world");
        $this->assertEquals("WebDriver module is not configured!\n \nHello world", $exception->getMessage());
    }

    public function testCanBeCreatedForModuleObject(): void
    {
        $exception = new \Codeception\Exception\ModuleConfigException(
            new \Codeception\Module\CodeHelper(make_container()),
            "Hello world"
        );
        $this->assertEquals("CodeHelper module is not configured!\n \nHello world", $exception->getMessage());
    }
}
