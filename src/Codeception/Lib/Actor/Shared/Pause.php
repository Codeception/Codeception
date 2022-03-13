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

        $logFile = '.pause.log';
        $relativeLogFilePath = codecept_relative_path(codecept_output_dir($logFile));
        $psyConf = new Configuration([
            'prompt' => '>> ',
            'startupMessage' => "<warning>Execution PAUSED</warning> use \$I-> to run commands.\nAll commands will be saved to $relativeLogFilePath"
        ]);
        $psyConf->setHistoryFile(codecept_output_dir($logFile));
        $psy = new Shell($psyConf);
        $psy->setScopeVariables(['I' => $this]);

        $psy->run();
    }
}
