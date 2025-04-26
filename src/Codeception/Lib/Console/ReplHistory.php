<?php

declare(strict_types=1);

namespace Codeception\Lib\Console;

class ReplHistory
{
    protected string $outputFile;

    protected array $stashedCommands = [];

    protected static ?self $instance = null;

    private function __construct()
    {
        $this->outputFile = codecept_output_dir('stashed-commands');

        if (file_exists($this->outputFile)) {
            unlink($this->outputFile);
        }
    }

    public static function getInstance(): ReplHistory
    {
        if (!static::$instance instanceof ReplHistory) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    public function add($command): void
    {
        $this->stashedCommands[] = $command;
    }

    public function getAll(): array
    {
        return $this->stashedCommands;
    }

    public function clear(): void
    {
        $this->stashedCommands = [];
    }

    public function save(): void
    {
        if ($this->stashedCommands === []) {
            return;
        }

        file_put_contents($this->outputFile, implode("\n", $this->stashedCommands) . "\n", FILE_APPEND);
        codecept_debug("Stashed commands have been saved to {$this->outputFile}");
        $this->clear();
    }
}
