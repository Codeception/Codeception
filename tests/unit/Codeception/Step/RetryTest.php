<?php
namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Util\Stub;

class RetryTest extends \PHPUnit\Framework\TestCase
{
    protected $shouldFail = true;

    public function testRetryStepShouldNotFailStep()
    {
        $moduleContainer = Stub::make(ModuleContainer::class, [
            'moduleForAction' => $this
        ]);
        $retry = new \Codeception\Step\Retry('_executeFailedCode', [], 2, 0);
        $retry->run($moduleContainer);

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