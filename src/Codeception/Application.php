<?php

namespace Codeception;

use Codeception\Exception\ConfigurationException;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{

    /**
     * @var ArgvInput
     */
    protected $coreArguments = null;

    /**
     * Register commands from config file
     *
     *  extensions:
     *      commands:
     *          - Project\Command\MyCustomCommand
     *
     */
    public function registerCustomCommands()
    {
        try {
            $this->readCustomCommandsFromConfig();
        } catch (ConfigurationException $e) {
            if ($e->getCode() === 404) {
                return;
            }
            $this->renderException($e, new ConsoleOutput());
            exit(1);
        } catch (\Exception $e) {
            $this->renderException($e, new ConsoleOutput());
            exit(1);
        }
    }

    /**
     * Search custom commands and register them.
     *
     * @throws ConfigurationException
     */
    protected function readCustomCommandsFromConfig()
    {
        $this->getCoreArguments(); // Maybe load outside configfile

        $config = Configuration::config();

        if (empty($config['extensions']['commands'])) {
            return;
        }

        foreach ($config['extensions']['commands'] as $commandClass) {
            $commandName = $this->getCustomCommandName($commandClass);
            $this->add(new $commandClass($commandName));
        }
    }

    /**
     * Validate and get the name of the command
     *
     * @param CustomCommandInterface $commandClass
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

        if (!in_array('Codeception\CustomCommandInterface', $interfaces)) {
            throw new ConfigurationException("Extension: Command {$commandClass} must implement " .
                                             "the interface `Codeception\\CustomCommandInterface`");
        }

        return $commandClass::getCommandName();
    }

    /**
     * To cache Class ArgvInput
     *
     * @inheritDoc
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if ($input === null) {
            $input = $this->getCoreArguments();
        }

        return parent::run($input, $output);
    }

    /**
     * Add global a --config option.
     *
     * @return InputDefinition
     */
    protected function getDefaultInputDefinition()
    {
        $inputDefinition = parent::getDefaultInputDefinition();
        $inputDefinition->addOption(
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config')
        );
        return $inputDefinition;
    }

    /**
     * Search for --config Option and if found will be loaded
     *
     * example:
     * -c file.yml|dir
     * -cfile.yml|dir
     * --config file.yml|dir
     * --config=file.yml|dir
     *
     * @return ArgvInput
     */
    protected function getCoreArguments()
    {
        if ($this->coreArguments !== null) {
            return $this->coreArguments;
        }

        $argv = $_SERVER['argv'];
        $argvWithoutConfig = [];

        for ($i = 0; $i < count($argv); $i++) {
            if (preg_match('/^(?:-([^c-]*)?c|--config(?:=|$))(.*)$/', $argv[$i], $match)) {
                if (!empty($match[2])) { //same index
                    $this->preloadConfiguration($match[2]);
                } elseif (isset($argv[$i + 1])) { //next index
                    $this->preloadConfiguration($argv[++$i]);
                }
                if (!empty($match[1])) {
                    $argvWithoutConfig[] = "-".$match[1]; //rest commands
                }
                continue;
            }
            $argvWithoutConfig[] = $argv[$i];
        }

        return $this->coreArguments = new ArgvInput($argvWithoutConfig);
    }

    /**
     * Pre load Configuration, the config option is use.
     *
     * @param string $configFile Path to Configuration
     *
     * @throws ConfigurationException
     */
    protected function preloadConfiguration($configFile)
    {
        try {
            Configuration::config($configFile);
        } catch (ConfigurationException $e) {
            if ($e->getCode() == 404) {
                throw new ConfigurationException("Your configuration file `{$configFile}` could not be found.", 405);
            }
            throw $e;
        }
    }
}
