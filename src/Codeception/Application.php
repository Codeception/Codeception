<?php

namespace Codeception;

use Codeception\Exception\ConfigurationException;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Output\ConsoleOutput;
use Codeception\Lib\Interfaces\CustomCommand;

class Application extends BaseApplication
{
    /**
     * Register commands from a config file
     *
     *  extensions:
     *      commands:
     *          - project\MyNewCommand
     *
     */
    public function registerCustomCommands()
    {
        $config = Configuration::config();

        if (empty($config['extensions']['commands'])) {
            return;
        }

        try {
            foreach ($config['extensions']['commands'] as $commandClass) {
                $commandName = $this->getCustomCommandName($commandClass);
                $this->add(new $commandClass($commandName));
            }
        } catch (\Exception $e) {
            $this->renderException($e, new ConsoleOutput());
            exit;
        }
    }

    /**
     * Validate and get the name of the command
     *
     * @param CustomCommand $commandClass
     *
     * @throws ConfigurationException
     *
     * @return string
     */
    protected function getCustomCommandName($commandClass)
    {
        if (!class_exists($commandClass)) {
            throw new ConfigurationException("Extension: Command class $commandClass not found");
        }

        $interfaces = class_implements($commandClass);

        if (!in_array('Codeception\Lib\Interfaces\CustomCommand', $interfaces)) {
            throw new ConfigurationException("Extension: Command {$commandClass} must implement the interface `Codeception\\Lib\\Interfaces\\CustomCommand`");
        }

        return $commandClass::getCommandName();
    }
}
