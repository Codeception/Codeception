<?php

declare(strict_types=1);

namespace Codeception\Extension;

use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use Symfony\Component\Process\Process;

use function array_shift;
use function class_exists;
use function count;
use function is_array;
use function sleep;

/**
 * Extension for execution of some processes before running tests.
 *
 * Processes can be independent and dependent.
 * Independent processes run independently of each other.
 * Dependent processes run sequentially one by one.
 *
 * Can be configured in suite config:
 *
 * ```yaml
 * # acceptance.suite.yml
 * extensions:
 *     enabled:
 *         - Codeception\Extension\RunBefore:
 *             - independent_process_1
 *             -
 *                 - dependent_process_1_1
 *                 - dependent_process_1_2
 *             - independent_process_2
 *             -
 *                 - dependent_process_2_1
 *                 - dependent_process_2_2
 * ```
 *
 * HINT: you can use different configurations per environment.
 */
class RunBefore extends Extension
{
    protected array $config = [];

    /**
     * @var array<string, string>
     */
    protected static array $events = [
        Events::SUITE_BEFORE => 'runBefore'
    ];

    private array $processes = [];

    public function _initialize(): void
    {
        if (!class_exists(Process::class)) {
            throw new ExtensionException($this, 'symfony/process package is required');
        }
    }

    public function runBefore(): void
    {
        $this->runProcesses();
        $this->processMonitoring();
    }

    private function runProcesses(): void
    {
        foreach ($this->config as $item) {
            if (is_array($item)) {
                $currentCommand = array_shift($item);
                $followingCommands = $item;
            } else {
                $currentCommand = $item;
                $followingCommands = [];
            }

            $process = $this->runProcess($currentCommand);
            $this->addProcessToMonitoring($process, $followingCommands);
        }
    }

    private function runProcess(string $command): Process
    {
        $this->output->debug('[RunBefore] Starting ' . $command);

        $process = Process::fromShellCommandline($command, $this->getRootDir());
        $process->start();

        return $process;
    }

    /**
     * @param string[] $followingCommands
     */
    private function addProcessToMonitoring(Process $process, array $followingCommands): void
    {
        $this->processes[] = [
            'instance' => $process,
            'following' => $followingCommands
        ];
    }

    private function removeProcessFromMonitoring(int $index): void
    {
        unset($this->processes[$index]);
    }

    private function processMonitoring(): void
    {
        while ($this->processes !== []) {
            $this->checkProcesses();
            sleep(1);
        }
    }

    private function checkProcesses(): void
    {
        foreach ($this->processes as $index => $process) {
            /**
             * @var Process $processInstance
             */
            $processInstance = $process['instance'];

            if (!$this->isRunning($processInstance)) {
                if (!$processInstance->isSuccessful()) {
                    $this->output->debug('[RunBefore] Failed ' . $processInstance->getCommandLine());
                    $this->output->writeln('<error>' . $processInstance->getErrorOutput() . '</error>');
                    exit(1);
                }

                $this->output->debug('[RunBefore] Completed ' . $processInstance->getCommandLine());
                $this->runFollowingCommand($process['following']);
                $this->removeProcessFromMonitoring($index);
            }
        }
    }

    /**
     * @param string[] $followingCommands
     */
    private function runFollowingCommand(array $followingCommands): void
    {
        if ($followingCommands !== []) {
            $process = $this->runProcess(array_shift($followingCommands));
            $this->addProcessToMonitoring($process, $followingCommands);
        }
    }

    private function isRunning(Process $process): bool
    {
        return $process->isRunning();
    }
}
