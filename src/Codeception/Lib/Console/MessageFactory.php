<?php
namespace Codeception\Lib\Console;

use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Diff\Differ;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * MessageFactory
 **/
class MessageFactory
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * MessageFactory constructor.
     * @param Output $output
     */
    public function __construct(Output $output)
    {
        $this->output = $output;
    }

    /**
     * @param string $text
     * @return Message
     */
    public function message($text = '')
    {
        return new Message($text, $this->output);
    }

    /**
     * @param ComparisonFailure $failure
     * @return Message|null
     */
    public function prepareCompMessage(ComparisonFailure $failure)
    {
        $compMessage = $failure->getMessage();
        $diff = $this->getDiff($failure->getExpectedAsString(), $failure->getActualAsString());

        if (!$diff) {
            return null;
        }

        return $this
            ->message($compMessage)
            ->append($diff);
    }

    /**
     * @param string $expected
     * @param string $actual
     * @return string
     */
    private function getDiff($expected = '', $actual = '')
    {
        if (!$actual && !$expected) {
            return '';
        }

        $differ = new Differ("- Expected | + Actual\n");

        return $differ->diff($expected, $actual);
    }
}
