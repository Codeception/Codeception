<?php

declare(strict_types=1);

namespace Codeception\Util;

use Codeception\Lib\Console\Output;
use Codeception\Lib\PauseShell;
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
     */
    public static function debug(mixed $message): void
    {
        self::$output?->debug($message);
    }

    public static function isEnabled(): bool
    {
        return self::$output instanceof Output;
    }

    public static function pause(array $vars = []): void
    {
        if (!self::isEnabled()) {
            return;
        }

        $pauseShell = new PauseShell();
        $psy = $pauseShell->getShell();
        $psy->setScopeVariables($vars);

        foreach (debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3) as $backtraceStep) {
            $class = $backtraceStep['class'] ?? null;
            $fn = $backtraceStep['function'] ?? null;

            if (
                ($class === self::class && $fn === 'pause') ||
                ($fn === 'codecept_pause' && !$class) ||
                !isset($backtraceStep['object'])
            ) {
                continue;
            }

            $pauseShell->addMessage('Use $this-> to access current object');
            $psy->setBoundObject($backtraceStep['object']);
            break;
        }

        $psy->run();
    }

    public static function confirm($question)
    {
        if (!self::$output instanceof Output) {
            return null;
        }

        return (new QuestionHelper())
            ->ask(new ArgvInput(), self::$output, new ConfirmationQuestion($question));
    }
}
