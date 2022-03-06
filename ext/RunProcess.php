<?php

declare(strict_types=1);

namespace Codeception\Extension;

use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use Symfony\Component\Process\Process;

use function array_reverse;
use function class_exists;
use function is_int;
use function sleep;

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
    /**
     * @var array<int|string, mixed>
     */
    protected array $config = ['sleep' => 0];

    /**
     * @var array<string, string>
     */
    protected static array $events = [
        Events::SUITE_BEFORE => 'runProcess',
        Events::SUITE_AFTER => 'stopProcess'
    ];

    /**
     * @var Process[]
     */
    private array $processes = [];

    public function _initialize(): void
    {
        if (!class_exists(Process::class)) {
            throw new ExtensionException($this, 'symfony/process package is required');
        }
    }

    public function runProcess(): void
    {
        $this->processes = [];
        foreach ($this->config as $key => $command) {
            if (!$command) {
                continue;
            }
            if (!is_int($key)) {
                continue; // configuration options
            }
            $process = Process::fromShellCommandline($command, $this->getRootDir(), null, null, null);
            $process->start();
            $this->processes[] = $process;
            $this->output->debug('[RunProcess] Starting ' . $command);
        }
        sleep($this->config['sleep']);
    }

    public function __destruct()
    {
        $this->stopProcess();
    }

    public function stopProcess(): void
    {
        foreach (array_reverse($this->processes) as $process) {
            /** @var Process $process */
            if (!$process->isRunning()) {
                continue;
            }
            $this->output->debug('[RunProcess] Stopping ' . $process->getCommandLine());
            $process->stop();
        }
        $this->processes = [];
    }

    /**
     * Disable the deserialization of the class to prevent attacker executing
     * code by leveraging the __destruct method.
     *
     * @see https://owasp.org/www-community/vulnerabilities/PHP_Object_Injection
     */
    public function __sleep()
    {
        throw new \BadMethodCallException('Cannot serialize ' . __CLASS__);
    }

    /**
     * Disable the deserialization of the class to prevent attacker executing
     * code by leveraging the __destruct method.
     *
     * @see https://owasp.org/www-community/vulnerabilities/PHP_Object_Injection
     */
    public function __wakeup()
    {
        throw new \BadMethodCallException('Cannot unserialize ' . __CLASS__);
    }
}
