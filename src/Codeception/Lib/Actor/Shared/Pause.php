<?php

declare(strict_types=1);

namespace Codeception\Lib\Actor\Shared;

use Codeception\Util\Debug;
use Psy\Shell;
use Psy\Configuration;

trait Pause
{
    public function pause(): void
    {
        if (!Debug::isEnabled()) {
            return;
        }

        $psyConf = new Configuration([
            'prompt' => '>> ',
            'startupMessage' => '<warning>Execution PAUSED</warning> use $I-> to run commands'
        ]);
        $psy = new Shell($psyConf);
        $psy->setScopeVariables(['I' => $this]);

        $psy->run();
    }
}
