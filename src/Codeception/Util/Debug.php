<?php
namespace Codeception\Util;

class Debug {

    /**
     * @var Console\Output null
     */
    protected static $output = null;

    public static function setOutput(Console\Output $output)
    {
        self::$output = $output;
    }

    static function debug($message)
    {
        if (!self::$output) return;
        self::$output->debug($message);
    }

    static function pause()
    {
        if (!self::$output) return;
        self::$output->writeln("<info>The execution paused. Press ENTER to continue</info>");
        if (trim(fgets(fopen("php://stdin","r"))) != chr(13)) return;
    }



}