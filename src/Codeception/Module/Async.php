<?php

namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\Module\Async\IPC;
use Codeception\Module\Async\Process;
use Codeception\Test\Cest;
use Codeception\TestInterface;
use Exception;
use Symfony\Component\Process\PhpProcess;
use function assert;
use function count;
use function get_class;
use function join;
use function register_shutdown_function;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function var_export;
use const PHP_EOL;

class Async extends CodeceptionModule
{
    const RESULT_CHANNEL = 'result';
    const MESSAGES_CHANNEL = 'messages';

    protected $requiredFields = [
        'autoload_path',
    ];

    /**
     * @var TestInterface|null
     */
    private $currentTest = null;

    /**
     * @var Process[]
     */
    private $processes = [];

    public function _before(TestInterface $test)
    {
        $this->currentTest = $test;
    }

    public function _after(TestInterface $test)
    {
        $this->processes = [];
        $this->currentTest = null;
    }

    /**
     * @param string $filename
     * @param string $class
     * @param string $method
     * @param array $params
     * @return Process
     */
    private function addProcess($filename, $class, $method, $params)
    {
        $inputFilename = tempnam(sys_get_temp_dir(), 'codecept_async_input');
        register_shutdown_function('unlink', $inputFilename);

        $outputFilename = tempnam(sys_get_temp_dir(), 'codecept_async_output');
        register_shutdown_function('unlink', $outputFilename);

        $ipc = new IPC($outputFilename, $inputFilename);

        $code = $this->generateCode($inputFilename, $outputFilename, $filename, $class, $method, $params);
        $this->debug($code);

        $process = new PhpProcess($code, null, null, 3);
        $this->debug($process->getCommandLine());

        $handle = sprintf('%s:%s[%d]', $class, $method, count($this->processes));
        return $this->processes[$handle] = new Process($handle, $process, $ipc);
    }

    /**
     * @return string|null
     */
    private function getAutoloadPath()
    {
        return $this->config['autoload_path'];
    }

    /**
     * @param string $inputFilename
     * @param string $outputFilename
     * @param string $file
     * @param string $class
     * @param string $method
     * @param array $params
     * @return string
     */
    private function generateCode($inputFilename, $outputFilename, $file, $class, $method, array $params)
    {
        $lines = ['<?php'];

        if (!empty($this->getAutoloadPath())) {
            $lines[] = sprintf('require_once %s;', var_export($this->getAutoloadPath(), true));
        }

        $lines[] = sprintf('require_once %s;', var_export($file, true));

        $lines[] = sprintf(
            "(new %s(%s, %s, %s, %s))->run(%s);",
            CodeceptionModule\Async\AsyncSlave::class,
            var_export($inputFilename, true),
            var_export($outputFilename, true),
            var_export($class, true),
            var_export($method, true),
            var_export($params, true)
        );

        return join(PHP_EOL, $lines);
    }

    /**
     * @param string $methodName
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function haveAsyncMethodRunning($methodName, array $params = [])
    {
        $currentTest = $this->currentTest;

        if (!$currentTest instanceof Cest) {
            throw new Exception('Invalid test type');
        }

        $process = $this->addProcess(
            $currentTest->getFileName(),
            get_class($currentTest->getTestClass()),
            $methodName,
            $params
        );

        $process->getProcess()->start(function ($type, $data) use ($methodName, $currentTest) {
            $this->debug(
                sprintf(
                    '%s::%s [%s] %s',
                    get_class($currentTest->getTestClass()),
                    $methodName,
                    $type,
                    $data
                )
            );
        });

        return $process->getHandle();
    }

    /**
     * @param string $handle
     * @return bool
     */
    public function seeAsyncMethodFinished($handle)
    {
        return $this->getProcess($handle)->getProcess()->isTerminated();
    }

    /**
     * @param string $handle
     * @return bool
     */
    public function seeAsyncMethodFinishedSuccessfully($handle)
    {
        $process = $this->getProcess($handle)->getProcess();
        return $process->isTerminated() && $process->isSuccessful();
    }

    /**
     * @param string $handle
     * @return Process
     */
    private function getProcess($handle)
    {
        assert(isset($this->processes[$handle]));
        return $this->processes[$handle];
    }

    /**
     * @param string $handle
     * @return Process
     */
    private function getFinishedProcess($handle)
    {
        $process = $this->getProcess($handle);

        if ($process->getProcess()->isRunning()) {
            $process->getProcess()->wait();
        }

        return $process;
    }

    /**
     * @param string $handle
     * @return mixed
     * @throws Exception
     */
    public function grabAsyncMethodReturnValue($handle)
    {
        return $this->getFinishedProcess($handle)->getReturnValue();
    }

    /**
     * @param string $handle
     * @return int
     */
    public function grabAsyncMethodStatusCode($handle)
    {
        return $this->getFinishedProcess($handle)->getProcess()->getExitCode();
    }

    /**
     * @param string $handle
     * @return string
     */
    public function grabAsyncMethodOutput($handle)
    {
        return $this->getFinishedProcess($handle)->getProcess()->getOutput();
    }

    /**
     * @param string $handle
     * @return string
     */
    public function grabAsyncMethodErrorOutputSoFar($handle)
    {
        return $this->getProcess($handle)->getProcess()->getErrorOutput();
    }

    /**
     * @param string $handle
     * @return string
     */
    public function grabAsyncMethodErrorOutput($handle)
    {
        return $this->getFinishedProcess($handle)->getProcess()->getErrorOutput();
    }

    /**
     *
     */
    public function haveAllAsyncMethodsFinished()
    {
        foreach ($this->processes as $process) {
            if ($process->getProcess()->isRunning()) {
                $process->getProcess()->wait();
            }
        }
    }

    /**
     * @param string $handle
     * @return mixed
     * @throws Exception
     */
    public function read($handle)
    {
        return $this->getProcess($handle)->getIpc()->read(self::MESSAGES_CHANNEL);
    }

    /**
     * @param string $handle
     * @param mixed $message
     * @throws Exception
     */
    public function write($handle, $message)
    {
        $this->getProcess($handle)->getIpc()->write(self::MESSAGES_CHANNEL, $message);
    }
}
