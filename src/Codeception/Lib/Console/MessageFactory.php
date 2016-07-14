<?php
namespace Codeception\Lib\Console;

use SebastianBergmann\Comparator\ComparisonFailure;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * MessageFactory
 **/
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

    /**
     * MessageFactory constructor.
     * @param Output $output
     */
    public function __construct(Output $output)
    {
        $this->output = $output;
        $this->diffFactory = new DiffFactory();
        $this->colorizer = new Colorizer();
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
    public function prepareComparisonFailureMessage(ComparisonFailure $failure)
    {
        $message = $this->message($failure->getMessage());
        $diff = $this->diffFactory->createDiff($failure);
        if (!$diff) {
            return $message;
        }
        $diff = $this->colorizer->colorize($diff);

        return $message->append("\n<comment>- Expected</comment> | <info>+ Actual</info>\n")->append($diff);
    }
}
