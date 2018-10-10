<?php

namespace Codeception\Lib;

/**
 * Populates a db using a parameterized command built from the Db module configuration.
 */
class DbPopulator
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $commands;

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

        //Convert To Array Format
        if (isset($this->config['dump']) && !is_array($this->config['dump'])) {
            $this->config['dump'] = array($this->config['dump']);
        }
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
     * @param string|null $dumpFile The dump file to build the command with.
     * @return string The resulting command string after evaluating any configuration's key
     */
    protected function buildCommand($command, $dumpFile = null)
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
        unset($vars['dump']); //JIC Dumpfile is Null and Dump is an Array (Shouldn't Happen)

        if ($dumpFile !== null) {
            $vars['dump'] = $dumpFile;
        }

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
        if (!isset($this->config['dump']) || $this->config['dump'] === false) {
            return $this->runCommand((string) $this->config['populator']);
        }

        foreach ($this->buildCommands() as $command) {
            $this->runCommand($command);
        }

        return true;
    }

    private function runCommand($command)
    {
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

    private function buildCommands()
    {
        if ($this->commands !== null) {
            return $this->commands;
        } else if (!isset($this->config['dump'])) {
            return $this->buildCommand($this->config['populator']);
        }

        $this->commands = array();

        foreach ($this->config['dump'] as $dumpFile) {
            $this->commands[] = $this->buildCommand($this->config['populator'], $dumpFile);
        }

        return $this->commands;
    }

    /**
     * @return array|string
     */
    public function getBuiltCommand()
    {
        $commands = $this->buildCommands();
        if (empty($commands)) {
            return '';
        } elseif (is_string($commands)) {
            return $commands;
        }

        return count($commands) > 1 ? $commands : $commands[0]; //Only Return First Instance If Only One Command
    }
}
