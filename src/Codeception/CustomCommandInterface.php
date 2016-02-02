<?php

namespace Codeception;

interface CustomCommandInterface
{

    /**
     * returns the name of the command
     *
     * @return string
     */
    public static function getCommandName();
}
