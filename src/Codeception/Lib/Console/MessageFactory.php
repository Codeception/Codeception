<?php

declare(strict_types=1);

namespace Codeception\Lib\Console;

use SebastianBergmann\Comparator\ComparisonFailure;

class MessageFactory
{
    /**
     * @var DiffFactory
     */
    protected $diffFactory;
    /**
     * @var Output
     */
    private $output;

    /**
     * @var Colorizer
     */
    protected $colorizer;

    public function __construct(Output $output)
    {
        $this->output = $output;
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
        if ($diff !== '') {
            return '';
        }
        $diff = $this->colorizer->colorize($diff);

        return "\n<comment>- Expected</comment> | <info>+ Actual</info>\n{$diff}";
    }
}
