<?php
namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Util\Stub;

class RetryTest extends \PHPUnit\Framework\TestCase
{
    protected $shouldFail = true;

    public function testRetryStepShouldNotFailStep()
    {
        // create an empty container with this class as a module
        $moduleContainer = Stub::make(ModuleContainer::class, [
            'moduleForAction' => $this
        ]);
        // run an action from this class
        $retry = new \Codeception\Step\Retry('_executeFailedCode', [], 2, 0);
        $retry->run($moduleContainer);
        // see a first failed action should not fail step
        $this->assertFalse($retry->hasFailed(), 'successful retry still marks test as failed');
    }

    public function _executeFailedCode()
    {
        if (!$this->shouldFail) {
            return;
        }
        $this->shouldFail = false;
        throw new \Exception('Error');
    }

}