<?php

declare(strict_types=1);

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\ModuleContainer;
use Codeception\Module\CodeHelper;
use Codeception\Stub;
use PHPUnit\Framework\TestCase;

class ModuleConfigTest extends TestCase
{
    public function testCanBeCreatedForModuleName()
    {
        $exception = new ModuleConfigException('Codeception\Module\WebDriver', "Hello world");
        $this->assertSame("WebDriver module is not configured!\n \nHello world", $exception->getMessage());
    }

    public function testCanBeCreatedForModuleObject()
    {
        $exception = new ModuleConfigException(
            new CodeHelper(Stub::make(ModuleContainer::class)),
            "Hello world"
        );
        $this->assertSame("CodeHelper module is not configured!\n \nHello world", $exception->getMessage());
    }
}
