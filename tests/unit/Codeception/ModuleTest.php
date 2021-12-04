<?php

declare(strict_types=1);

use Codeception\Stub;

class ModuleTest extends \Codeception\PHPUnit\TestCase
{
    public function testRequirements()
    {
        $module = Stub::make('ModuleStub');
        try {
            $module->_setConfig([]);
        } catch (\Exception $exception) {
            $this->assertStringContainsString('"error"', $exception->getMessage());
            $this->assertStringContainsString('no\such\class', $exception->getMessage());
            $this->assertStringContainsString('composer', $exception->getMessage());
            $this->assertStringNotContainsString('installed', $exception->getMessage());
            return;
        }

        $this->fail('no exception thrown');
    }
}

class ModuleStub extends \Codeception\Module implements \Codeception\Lib\Interfaces\RequiresPackage
{
    /**
     * @return array<string, string>
     */
    public function _requires(): array
    {
        return ['no\such\class' => '"error"', \Codeception\Module::class => '"installed"'];
    }
}
