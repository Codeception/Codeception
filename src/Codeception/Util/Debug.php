<?php

namespace Codeception\Util;

class Debug
{
    /**
     * @var Console\Output null
     */
    protected static $output = null;

    public static function setOutput(Console\Output $output)
    {
        self::$output = $output;
    }

    public static function debug($message)
    {
        if (!self::$output) {
            return;
        }
        self::$output->debug($message);
    }

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
}
