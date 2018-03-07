<?php

use Codeception\Util\Stub;

class ModuleTest extends \PHPUnit\Framework\TestCase
{
    public function testRequirements()
    {
        $module = Stub::make('ModuleStub');
        try {
            $module->_setConfig([]);
        } catch (\Exception $e) {
            $this->assertContains('"error"', $e->getMessage());
            $this->assertContains('no\such\class', $e->getMessage());
            $this->assertContains('composer', $e->getMessage());
            $this->assertNotContains('installed', $e->getMessage());
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
