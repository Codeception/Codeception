<?php
namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Util\Stub;

class TryToTest extends \PHPUnit\Framework\TestCase
{
    protected $shouldFail = true;

    public function testTryToShouldReturnSuccess()
    {
        $moduleContainer = Stub::make(ModuleContainer::class, [
            'moduleForAction' => $this
        ]);
        $try = new \Codeception\Step\TryTo('_executeFailedCode', []);
        $val = $try->run($moduleContainer);
        $this->assertEquals(false, $val);
    }

    public function testTryStepShouldNotFailStep()
    {
        $moduleContainer = Stub::make(ModuleContainer::class, [
            'moduleForAction' => $this
        ]);
        $try = new \Codeception\Step\TryTo('_executeFailedCode', []);
        $try->run($moduleContainer);

        $this->assertFalse($try->hasFailed(), 'successful retry still marks test as failed');
    }

    public function _executeFailedCode()
    {
        throw new \Exception('Error');
    }

}