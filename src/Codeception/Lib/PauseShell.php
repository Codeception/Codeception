<?php

declare(strict_types=1);

namespace Codeception\Lib;

use Psy\Configuration;
use Psy\Shell;

class PauseShell
{
    public const LOG_FILE = '.pause.log';
    private readonly Configuration $psyConf;

    public function __construct()
    {
        $relativeLogFilePath = codecept_relative_path(codecept_output_dir(self::LOG_FILE));
        $this->psyConf = new Configuration([
            'prompt' => '>> ',
            'startupMessage' => "<warning>Execution PAUSED</warning> All commands will be saved to $relativeLogFilePath",
            'historyFile' => codecept_output_dir(self::LOG_FILE),
            'historySize' => 1000,
        ]);
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
