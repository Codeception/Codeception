<?php

declare(strict_types=1);

namespace Codeception\Util;

use Codeception\Lib\Console\Output;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * This class is used only when Codeception is executed in `--debug` mode.
 * In other cases method of this class won't be seen.
 */
class Debug
{
    protected static ?Output $output = null;

    public static function setOutput(Output $output): void
    {
        self::$output = $output;
    }

    /**
     * Prints data to screen. Message can be any time of data
     *
     * @param mixed $message
     */
    public static function debug($message): void
    {
        if (!self::$output) {
            return;
        }
        self::$output->debug($message);
    }

    public static function isEnabled(): bool
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
