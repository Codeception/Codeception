<?php

use Codeception\Event\PrintResultEvent;
use Codeception\Events;
use Codeception\Extension;
use Symfony\Component\Console\Output\OutputInterface;

class VerbosityLevelOutput extends Extension
{
    /** @var array */
    public static $events = [
        Events::RESULT_PRINT_AFTER => 'printResult',
    ];

    public function printResult(PrintResultEvent $e)
    {
        $this->writeln(var_export($this->options, true));
        $this->writeln("Modules used: " . implode(', ', $this->getCurrentModuleNames()));

        if ($this->options['verbosity'] <= OutputInterface::VERBOSITY_NORMAL) {
            $this->writeln('Low verbosity');
        } elseif ($this->options['verbosity'] == OutputInterface::VERBOSITY_VERBOSE) {
            $this->writeln('Medium verbosity');
        } elseif ($this->options['verbosity'] == OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $this->writeln('High verbosity');
        } else {
            $this->writeln('Extreme verbosity');
        }
    }
}
