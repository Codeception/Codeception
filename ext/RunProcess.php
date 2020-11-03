<?php

namespace Codeception\Extension;

use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use Symfony\Component\Process\Process;

/**
 * Extension to start and stop processes per suite.
 * Can be used to start/stop selenium server, chromedriver, mailcatcher, etc.
 *
 * Can be configured in suite config:
 *
 * ```yaml
 * # acceptance.suite.yml
 * extensions:
 *     enabled:
 *         - Codeception\Extension\RunProcess:
 *             - chromedriver
 * ```
 *
 * Multiple parameters can be passed as array:
 *
 * ```yaml
 * # acceptance.suite.yml
 *
 * extensions:
 *     enabled:
 *         - Codeception\Extension\RunProcess:
 *             - php -S 127.0.0.1:8000 -t tests/data/app
 *             - java -jar ~/selenium-server.jar
 * ```
 *
 * In the end of a suite all launched processes will be stopped.
 *
 * To wait for the process to be launched use `sleep` option.
 * In this case you need configuration to be specified as object:
 *
 * ```yaml
 * extensions:
 *     enabled:
 *         - Codeception\Extension\RunProcess:
 *             0: java -jar ~/selenium-server.jar
 *             1: mailcatcher
 *             sleep: 5 # wait 5 seconds for processes to boot
 * ```
 *
 * HINT: you can use different configurations per environment.
 */
class RunProcess extends Extension
{
    protected $config = ['sleep' => 0];

    protected static $events = [
        Events::SUITE_BEFORE => 'runProcess',
        Events::SUITE_AFTER => 'stopProcess'
    ];

    private $processes = [];

    public function _initialize()
    {
        if (!class_exists('Symfony\Component\Process\Process')) {
            throw new ExtensionException($this, 'symfony/process package is required');
        }
    }

    public function runProcess()
    {
        $this->processes = [];
        foreach ($this->config as $key => $command) {
            if (!$command) {
                continue;
            }
            if (!is_int($key)) {
                continue; // configuration options
            }
            if (method_exists(Process::class, 'fromShellCommandline')) {
                //Symfony 4.2+
                $process = Process::fromShellCommandline($command, $this->getRootDir(), null, null, null);
            } else {
                $process = new Process($command, $this->getRootDir(), null, null, null);
            }
            $process->start();
            $this->processes[] = $process;
            $this->output->debug('[RunProcess] Starting '.$command);
        }
        sleep($this->config['sleep']);
    }

    public function __destruct()
    {
        $this->stopProcess();
    }

    public function stopProcess()
    {
        foreach (array_reverse($this->processes) as $process) {
            /** @var $process Process  **/
            if (!$process->isRunning()) {
                continue;
            }
            $this->output->debug('[RunProcess] Stopping ' . $process->getCommandLine());
            $process->stop();
        }
        $this->processes = [];
    }
}
