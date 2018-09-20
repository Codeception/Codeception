<?php
namespace Codeception\Util;

use Codeception\Lib\Console\Output;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * This class is used only when Codeception is executed in `--debug` mode.
 * In other cases method of this class won't be seen.
 */
class Debug
{
    /**
     * @var Output null
     */
    protected static $output = null;

    public static function setOutput(Output $output)
    {
        self::$output = $output;
    }

    /**
     * Prints data to screen. Message can be any time of data
     *
     * @param $message
     */
    public static function debug($message)
    {
        if (!self::$output) {
            return;
        }
        self::$output->debug($message);
    }

    /**
     * Pauses execution and waits for user input to proceed.
     */
    public static function pause()
    {
        if (!self::$output) {
            return;
        }

        self::$output->writeln("<info>The execution has been paused. Press ENTER to continue</info>");

        if (trim(fgets(STDIN)) != chr(13)) {
            return;
        }
    }

    public static function isEnabled()
    {
        return (bool) self::$output;
    }

    public static function confirm($question)
    {
        if (!self::$output) {
            return;
        }

        $questionHelper = new QuestionHelper();
        return $questionHelper->ask(new ArgvInput(), self::$output, new ConfirmationQuestion($question));
    }
}
