<?php

declare(strict_types=1);

namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Stub;

class TryToTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var bool
     */
    protected bool $shouldFail = true;

    public function testTryToShouldReturnSuccess()
    {
        // create an empty container with this class as a module
        $moduleContainer = Stub::make(ModuleContainer::class, [
            'moduleForAction' => $this
        ]);
        // run an action from this class
        $tryTo = new \Codeception\Step\TryTo('_executeFailedCode', []);
        $val = $tryTo->run($moduleContainer);
        // see a failed action returns false
        $this->assertFalse($val);
    }

    public function testTryStepShouldNotFailStep()
    {
        // run an action from this class
        $tryTo = new \Codeception\Step\TryTo('_executeFailedCode', []);
        $moduleContainer = Stub::make(ModuleContainer::class, [
            'moduleForAction' => $this
        ]);
        $tryTo->run($moduleContainer);
        // see a failed action should not fail
        $this->assertFalse($tryTo->hasFailed(), 'successful retry still marks test as failed');
    }

    public function _executeFailedCode()
    {
        throw new \Exception('Error');
    }
}
