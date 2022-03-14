<?php

declare(strict_types=1);

namespace Codeception\Lib\Actor\Shared;

use Codeception\Command\Console;
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

        $logFile = '.pause.log';
        $relativeLogFilePath = codecept_relative_path(codecept_output_dir($logFile));
        $psyConf = new Configuration([
            'prompt' => '>> ',
            'startupMessage' => "<warning>Execution PAUSED</warning> use \$I-> to run commands.\nAll commands will be saved to $relativeLogFilePath"
        ]);
        $psyConf->setHistoryFile(codecept_output_dir($logFile));
        $psy = new Shell($psyConf);
        $psy->setScopeVariables(['I' => $this]);
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        if (!$backtrace[1]['object'] instanceof Console) {
            // set the scope of test class
            $psy->setBoundObject($backtrace[1]['object']);
        }
        $psy->run();
    }
}
