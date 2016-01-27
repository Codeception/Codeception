<?php

namespace Codeception;

use Codeception\Exception\ConfigurationException;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Output\ConsoleOutput;
use Codeception\Lib\Interfaces\CustomCommands;

class Application extends BaseApplication
{
    /**
     * Register Commands from a Config file
     *
     *  extensions:
     *      commands:
     *          - project\MyNewCommand
     *
     */
    public function registerCustomCommands()
    {
        $aCommands = Configuration::config();

        if (empty($aCommands['extensions']['commands'])) {
            return;
        }

        try {
            foreach ($aCommands['extensions']['commands'] as $sCommandClass) {
                $sCommandName = $this->_getCommandName($sCommandClass);
                $this->add(new $sCommandClass($sCommandName));
            }
        } catch (\Exception $e) {
            $this->renderException($e, new ConsoleOutput());
            exit;
        }
    }

    /**
     * Validate and Get the Command Name
     *
     * @param CustomCommands $sCommandClass
     *
     * @throws ConfigurationException
     *
     * @return string
     */
    protected function _getCommandName($sCommandClass)
    {
        if (!class_exists($sCommandClass)) {
            throw new ConfigurationException("Extension: Command class $sCommandClass not found");
        }

        $aInterfaces = class_implements($sCommandClass);

        if (!in_array('Codeception\Lib\Interfaces\CustomCommands', $aInterfaces)) {
            throw new ConfigurationException("Extension: Command $sCommandClass has not the Interface `Codeception\\Lib\\Interfaces\\CustomCommands`");
        }

        return $sCommandClass::getCommandName();
    }
}
