<?php
namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Util\Stub;

class TryToTest extends \PHPUnit\Framework\TestCase
{
    protected $shouldFail = true;

    public function testTryToShouldReturnSuccess()
    {
        // create an empty container with this class as a module
        $moduleContainer = Stub::make(ModuleContainer::class, [
            'moduleForAction' => $this
        ]);
        // run an action from this class
        $try = new \Codeception\Step\TryTo('_executeFailedCode', []);
        $val = $try->run($moduleContainer);
        // see a failed action returns false
        $this->assertFalse($val);
    }

    public function testTryStepShouldNotFailStep()
    {
        $moduleContainer = Stub::make(ModuleContainer::class, [
            'moduleForAction' => $this
        ]);
        // run an action from this class
        $try = new \Codeception\Step\TryTo('_executeFailedCode', []);
        $try->run($moduleContainer);
        // see a failed action should not fail
        $this->assertFalse($try->hasFailed(), 'successful retry still marks test as failed');
    }

    public function _executeFailedCode()
    {
        throw new \Exception('Error');
    }

}