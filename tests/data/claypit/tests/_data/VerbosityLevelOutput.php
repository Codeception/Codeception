<?php

use Codeception\Event\PrintResultEvent;
use Codeception\Events;
use Codeception\Platform\Extension;

class VerbosityLevelOutput extends Extension
{
    public static $events = array(
        Events::RESULT_PRINT_AFTER => 'printResult',
    );

    public function printResult(PrintResultEvent $e)
    {
        if ($this->options['verbosity'] <= 1) {
            $this->writeln('Low verbosity');
        } else if ($this->options['verbosity'] == 2) {
            $this->writeln('Medium verbosity');
        } else if ($this->options['verbosity'] == 3) {
            $this->writeln('High verbosity');
        } else {
            $this->writeln('Extreme verbosity');
        }
    }
}
