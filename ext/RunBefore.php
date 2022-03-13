<?php

namespace Codeception\Extension;

use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use Symfony\Component\Process\Process;

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
    protected $config = [];

    protected static $events = [
        Events::SUITE_BEFORE => 'runBefore'
    ];

    /** @var array[] */
    private $processes = [];

    public function _initialize()
    {
        if (!class_exists('Symfony\Component\Process\Process')) {
            throw new ExtensionException($this, 'symfony/process package is required');
        }
    }

    public function runBefore()
    {
        $this->runProcesses();
        $this->processMonitoring();
    }

    private function runProcesses()
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

    /**
     * @param string $command
     * @return Process
     */
    private function runProcess($command)
    {
        $this->output->debug('[RunBefore] Starting ' . $command);

        if (method_exists(Process::class, 'fromShellCommandline')) {
            //Symfony 4.2+
            $process = Process::fromShellCommandline($command, $this->getRootDir());
        } else {
            $process = new Process($command, $this->getRootDir());
        }
        $process->start();

        return $process;
    }

    /**
     * @param string[] $followingCommands
     */
    private function addProcessToMonitoring(Process $process, array $followingCommands)
    {
        $this->processes[] = [
            'instance' => $process,
            'following' => $followingCommands
        ];
    }

    /**
     * @param int $index
     */
    private function removeProcessFromMonitoring($index)
    {
        unset($this->processes[$index]);
    }

    private function processMonitoring()
    {
        while (count($this->processes) !== 0) {
            $this->checkProcesses();
            sleep(1);
        }
    }

    private function checkProcesses()
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
    private function runFollowingCommand(array $followingCommands)
    {
        if (count($followingCommands) > 0) {
            $process = $this->runProcess(array_shift($followingCommands));
            $this->addProcessToMonitoring($process, $followingCommands);
        }
    }

    private function isRunning(Process $process)
    {
        if ($process->isRunning()) {
            return true;
        }
        return false;
    }
}
