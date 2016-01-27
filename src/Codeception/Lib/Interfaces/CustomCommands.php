<?php
/**
 * Created by solutionDrive GmbH
 *
 * @author    Tobias Matthaiou <tm@solutionDrive.de>
 * @date      26.01.16
 * @time      10:02
 * @copyright 2016 solutionDrive GmbH
 */

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
