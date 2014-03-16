<?php
namespace Codeception\Command\Shared;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

trait Style {

    public function addStyles(OutputInterface $output)
    {
        $style = new OutputFormatterStyle('white', 'green', array('bold'));
        $output->getFormatter()->setStyle('notice', $style);
    }

}