<?php

namespace Codeception\Lib\Interfaces;

interface CustomCommand
{

    /**
     * returns the name of the command
     *
     * @return string
     */
    public static function getCommandName();
}
