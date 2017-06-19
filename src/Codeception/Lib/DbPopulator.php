<?php

namespace Codeception\Lib;

/**
 * Populates a db using a parameterized command built from the Db module configuration.
 */
class DbPopulator
{
    /**
     * The command to be executed.
     *
     * @var string
     */
    private $builtCommand;

    /**
     * @var array
     */
    protected $config;

    /**
     * Constructs a DbPopulator object for the given command and Db module.
     *
     * @param $config
     * @internal param string $command The parameterized command to evaluate and execute later.
     * @internal param Codeception\Module\Db|null $dbModule The Db module used to build the populator command or null.
     */
    public function __construct($config)
    {
        $this->config = $config;
        $command = $this->config['populator'];
        $this->builtCommand = $this->buildCommand((string) $command);
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
    protected function buildCommand($command)
    {
        $dsn = isset($this->config['dsn']) ? $this->config['dsn'] : '';
        $dsnVars = [];
        $dsnWithoutDriver = preg_replace('/^[a-z]+:/i', '', $dsn);
        foreach (explode(';', $dsnWithoutDriver) as $item) {
            $keyValueTuple = explode('=', $item);
            if (count($keyValueTuple) > 1) {
                list($k, $v) = array_values($keyValueTuple);
                $dsnVars[$k] = $v;
            }
        }
        $vars = array_merge($dsnVars, $this->config);
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
     * @return bool
     */
    public function run()
    {
        $command = $this->getBuiltCommand();
        codecept_debug("[Db] Executing Populator: `$command`");

        exec($command, $output, $exitCode);

        if (0 !== $exitCode) {
            throw new \RuntimeException(
                "The populator command did not end successfully: \n" .
                "  Exit code: $exitCode \n" .
                "  Output:" . implode("\n", $output)
            );
        }

        codecept_debug("[Db] Populator Finished.");
        return true;
    }

    public function getBuiltCommand()
    {
        return $this->builtCommand;
    }
}
