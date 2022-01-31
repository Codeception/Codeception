<?php

declare(strict_types=1);

namespace Codeception\Lib\Console;

use SebastianBergmann\Comparator\ComparisonFailure;

class MessageFactory
{
    protected DiffFactory $diffFactory;

    protected Colorizer $colorizer;

    public function __construct(private Output $output)
    {
        $this->diffFactory = new DiffFactory();
        $this->colorizer = new Colorizer();
    }

    public function message(string $text = ''): Message
    {
        return new Message($text, $this->output);
    }

    public function prepareComparisonFailureMessage(ComparisonFailure $failure): string
    {
        $diff = $this->diffFactory->createDiff($failure);
        if ($diff === '') {
            return '';
        }
        $diff = $this->colorizer->colorize($diff);

        return "\n<comment>- Expected</comment> | <info>+ Actual</info>\n{$diff}";
    }
}
