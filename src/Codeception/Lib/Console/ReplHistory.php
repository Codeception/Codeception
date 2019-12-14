<?php

namespace Codeception\Lib\Console;

class ReplHistory
{
    protected $outputFile;

    protected $stashedCommands = [];

    /**
     * @var ReplHistory
     */
    protected static $instance;

    private function __construct()
    {
        $this->outputFile = codecept_output_dir('stashed-commands');

        if (file_exists($this->outputFile)) {
            unlink($this->outputFile);
        }
    }

    /**
     * @return ReplHistory
     */
    public static function getInstance()
    {
        if (static::$instance == null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function add($command)
    {
        $this->stashedCommands[] = $command;
    }

    public function getAll()
    {
        return $this->stashedCommands;
    }

    public function clear()
    {
        $this->stashedCommands = [];
    }

    public function save()
    {
        if (empty($this->stashedCommands)) {
            return;
        }

        file_put_contents($this->outputFile, implode("\n", $this->stashedCommands) . "\n", FILE_APPEND);

        codecept_debug("Stashed commands have been saved to {$this->outputFile}");

        $this->clear();
    }
}
