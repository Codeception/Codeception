<?php

declare(strict_types=1);

namespace Codeception\Lib\Actor\Shared;

use Codeception\Command\Console;
use Codeception\Lib\PauseShell;
use Codeception\Util\Debug;

trait Pause
{
    public function pause(array $vars = []): void
    {
        if (!Debug::isEnabled()) {
            return;
        }

        $psy = (new PauseShell())
            ->addMessage('$I-> to launch commands')
            ->addMessage('$this-> to access current test')
            ->addMessage('exit to exit')
            ->getShell();

        $vars['I'] = $this;
        $psy->setScopeVariables($vars);
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        if (!$backtrace[1]['object'] instanceof Console) {
            // set the scope of test class
            $psy->setBoundObject($backtrace[1]['object']);
        }
        $psy->run();
    }
}
