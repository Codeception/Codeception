<?php

namespace Codeception\Lib;

use Psy\Shell;
use Psy\Configuration;

class PauseShell
{
    public const LOG_FILE = '.pause.log';
    private Configuration $psyConf;

    public function __construct()
    {
        $relativeLogFilePath = codecept_relative_path(codecept_output_dir(self::LOG_FILE));
        $this->psyConf = new Configuration([
            'prompt' => '>> ',
            'startupMessage' => "<warning>Execution PAUSED</warning> All commands will be saved to $relativeLogFilePath"
        ]);
        $this->psyConf->setHistoryFile(codecept_output_dir(self::LOG_FILE));
        $this->psyConf->setHistorySize(1000);
    }

    public function addMessage(string $message): self
    {
        $this->psyConf->setStartupMessage($this->psyConf->getStartupMessage() . "\n" . $message);
        return $this;
    }

    public function getShell(): Shell
    {
        return new Shell($this->psyConf);
    }
}
