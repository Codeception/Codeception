<?php
namespace Codeception\Command\Shared;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

trait Style
{
    public function addStyles(OutputInterface $output)
    {
        $style = new OutputFormatterStyle('white', 'green', ['bold']);
        $output->getFormatter()->setStyle('notice', $style);
        $style = new OutputFormatterStyle(null, null, ['bold']);
        $output->getFormatter()->setStyle('bold', $style);
        $style = new OutputFormatterStyle(null, 'yellow', ['bold']);
        $output->getFormatter()->setStyle('warning', $style);
    }
}
