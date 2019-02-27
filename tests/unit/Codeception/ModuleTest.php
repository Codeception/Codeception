<?php

use Codeception\Util\Stub;

class ModuleTest extends \Codeception\PHPUnit\TestCase
{
    public function testRequirements()
    {
        $module = Stub::make('ModuleStub');
        try {
            $module->_setConfig([]);
        } catch (\Exception $e) {
            $this->assertStringContainsString('"error"', $e->getMessage());
            $this->assertStringContainsString('no\such\class', $e->getMessage());
            $this->assertStringContainsString('composer', $e->getMessage());
            $this->assertStringNotContainsString('installed', $e->getMessage());
            return;
        }
        $this->fail('no exception thrown');
    }
}

class ModuleStub extends \Codeception\Module implements \Codeception\Lib\Interfaces\RequiresPackage
{
    public function _requires()
    {
        return ['no\such\class' => '"error"', 'Codeception\Module' => '"installed"'];
    }
}
