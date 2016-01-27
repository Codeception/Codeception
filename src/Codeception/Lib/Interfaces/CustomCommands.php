<?php

namespace Codeception\Lib\Interfaces;

interface CustomCommands
{

    /**
     * returns the name of the command
     *
     * @return string
     */
    public static function getCommandName();
}
