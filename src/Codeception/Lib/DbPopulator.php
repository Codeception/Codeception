<?php

namespace Codeception\Lib;

use Codeception\Module\Db;

/**
 * Populates a db using a parameterized command built from the Db module configuration.
 */
class DbPopulator
{
    /**
     * The Db module that will be populated by the command.
     *
     * Used to extract its configuration values to build the "populator" command.
     *
     * @var Codeception/Module/Db
     */
    protected $dbModule;

    /**
     * The command to be executed.
     *
     * @var string
     */
    private $builtCommand;

    /**
     * Constructs a DbPopulator object for the given command and Db module.
     *
     * @param string  $command  The parameterized command to evaluate and execute later.
     * @param Codeception\Module\Db|null $dbModule The Db module used to build the populator command or null.
     */
    public function __construct($command, Db $dbModule = null)
    {
        $this->dbModule = $dbModule;
        $this->builtCommand = $this->buildCommand(
            (string) $command,
            $this->dbModule ? $this->dbModule->_getConfig() : []
        );
    }

    /**
     * Builds out a command replacing any found `$key` with its value if found in the given configuration.
     *
     * Process any $key found in the configuration array as a key of the array and replaces it with
     * the found value for the key. Example:
     *
     * ```php
     * <?php
     *
     * $command = 'Hello $name';
     * $config = ['name' => 'Mauro'];
     *
     * // With the above parameters it will return `'Hello Mauro'`.
     * ```
     *
     * @param string $command The command to be evaluated using the given config
     * @param array $config The configuration values used to replace any found $keys with values from this array.
     * @return string The resulting command string after evaluating any configuration's key
     */
    protected function buildCommand($command, $config = [])
    {
        $dsn = isset($config['dsn']) ? $config['dsn'] : '';
        $dsnVars = [];
        $dsnWithoutDriver = preg_replace('/^[a-z]+:/i', '', $dsn);
        foreach (explode(';', $dsnWithoutDriver) as $item) {
            $keyValueTuple = explode('=', $item);
            if (count($keyValueTuple) > 1) {
                list($k, $v) = array_values($keyValueTuple);
                $dsnVars[$k] = $v;
            }
        }
        $vars = array_merge($dsnVars, $config);
        foreach ($vars as $key => $value) {
            $vars['$'.$key] = $value;
            unset($vars[$key]);
        }
        return str_replace(array_keys($vars), array_values($vars), $command);
    }

    /**
     * Executes the command built using the Db module configuration.
     *
     * Uses the PHP `exec` to spin off a child process for the built command.
     *
     * @return array The resulting triple values (return output, full output text and exit code)
     * out from executing the command.
     */
    public function execute()
    {
        $command = $this->builtCommand;
        $ret = exec($command, $output, $exitCode);
        return [$ret, $output, $exitCode];
    }

    public function getBuiltCommand()
    {
        return $this->builtCommand;
    }
}
