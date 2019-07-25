<?php

namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\Module\Async\IPC;
use Codeception\Test\Cest;
use Codeception\TestInterface;
use Exception;
use Symfony\Component\Process\PhpProcess;
use function array_key_exists;
use function assert;
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
     * @var PhpProcess[]
     */
    private $processes = [];

    /**
     * @var IPC[]
     */
    private $masterControllers = [];

    /**
     * @var array
     */
    private $returnValues = [];

    /**
     * @var string[]
     */
    private $slaveControllerInputFilenames = [];

    /**
     * @var string[]
     */
    private $slaveControllerOutputFilenames = [];

    public function _before(TestInterface $test)
    {
        $this->currentTest = $test;
    }

    public function _after(TestInterface $test)
    {
        $this->processes = [];
        $this->currentTest = null;
        $this->slaveControllerInputFilenames = [];
        $this->slaveControllerOutputFilenames = [];
        $this->masterControllers = [];
        $this->returnValues = [];
    }

    /**
     * @return string
     */
    private function addProcess()
    {
        $handle = uniqid('codecept_async', true);

        $this->slaveControllerInputFilenames[$handle] = tempnam(sys_get_temp_dir(), 'codecept_async_input');
        register_shutdown_function('unlink', $this->slaveControllerInputFilenames[$handle]);

        $this->slaveControllerOutputFilenames[$handle] = tempnam(sys_get_temp_dir(), 'codecept_async_output');
        register_shutdown_function('unlink', $this->slaveControllerOutputFilenames[$handle]);

        $this->masterControllers[$handle] = new IPC(
            $this->slaveControllerOutputFilenames[$handle],
            $this->slaveControllerInputFilenames[$handle]
        );

        return $handle;
    }

    /**
     * @return string|null
     */
    private function getAutoloadPath()
    {
        return $this->config['autoload_path'];
    }

    /**
     * @param string $handle
     * @param string $file
     * @param string $class
     * @param string $method
     * @param array $params
     * @return string
     */
    private function generateCode($handle, $file, $class, $method, array $params)
    {
        $lines = ['<?php'];

        if (!empty($this->getAutoloadPath())) {
            $lines[] = sprintf('require_once %s;', var_export($this->getAutoloadPath(), true));
        }

        $lines[] = sprintf('require_once %s;', var_export($file, true));

        $lines[] = sprintf(
            "(new %s(%s, %s, %s, %s))->run(%s);",
            CodeceptionModule\Async\AsyncSlave::class,
            var_export($this->slaveControllerInputFilenames[$handle], true),
            var_export($this->slaveControllerOutputFilenames[$handle], true),
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

        $handle = $this->addProcess();

        $code = $this->generateCode($handle, $currentTest->getFileName(), get_class($currentTest->getTestClass()), $methodName, $params);

        $this->debug($code);

        $process = new PhpProcess(
            $code,
            null,
            null,
            3
        );

        $this->debug($process->getCommandLine());

        $this->processes[$handle] = $process;

        $process->start(function ($type, $data) use ($methodName, $currentTest) {
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

        return $handle;
    }

    /**
     * @param string $handle
     */
    public function seeAsyncMethodFinished($handle)
    {
        assert(isset($this->processes[$handle]));
        $process = $this->processes[$handle];
        $this->assertTrue($process->isTerminated());
    }

    /**
     * @param string $handle
     */
    public function seeAsyncMethodFinishedSuccessfully($handle)
    {
        assert(isset($this->processes[$handle]));
        $process = $this->processes[$handle];
        $this->assertTrue($process->isTerminated() && $process->isSuccessful());
    }

    /**
     * @param string $handle
     * @return PhpProcess
     */
    private function getProcess($handle)
    {
        assert(isset($this->processes[$handle]));
        return $this->processes[$handle];
    }

    /**
     * @param string $handle
     * @return PhpProcess
     */
    private function getFinishedProcess($handle)
    {
        $process = $this->getProcess($handle);

        if ($process->isRunning()) {
            $process->wait();
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
        $this->getFinishedProcess($handle);
        if (!array_key_exists($handle, $this->returnValues)) {
            $this->returnValues[$handle] = $this->masterControllers[$handle]->read(self::RESULT_CHANNEL);
        }
        return $this->returnValues[$handle];
    }

    /**
     * @param string $handle
     * @return int
     */
    public function grabAsyncMethodStatusCode($handle)
    {
        return $this->getFinishedProcess($handle)->getExitCode();
    }

    /**
     * @param string $handle
     * @return string
     */
    public function grabAsyncMethodOutput($handle)
    {
        return $this->getFinishedProcess($handle)->getOutput();
    }

    /**
     * @param string $handle
     * @return string
     */
    public function grabAsyncMethodErrorOutputSoFar($handle)
    {
        return $this->getProcess($handle)->getErrorOutput();
    }

    /**
     * @param string $handle
     * @return string
     */
    public function grabAsyncMethodErrorOutput($handle)
    {
        return $this->getFinishedProcess($handle)->getErrorOutput();
    }

    /**
     *
     */
    public function haveAllAsyncMethodsFinished()
    {
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                $process->wait();
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
        return $this->masterControllers[$handle]->read(self::MESSAGES_CHANNEL);
    }

    /**
     * @param string $handle
     * @param mixed $message
     * @throws Exception
     */
    public function write($handle, $message)
    {
        $this->masterControllers[$handle]->write(self::MESSAGES_CHANNEL, $message);
    }
}
