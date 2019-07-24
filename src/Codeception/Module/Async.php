<?php

namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\Module\Async\MasterController;
use Codeception\Module\Async\SlaveController;
use Codeception\Test\Cest;
use Codeception\TestInterface;
use Exception;
use Symfony\Component\Process\PhpProcess;
use function assert;
use function call_user_func_array;
use function file_get_contents;
use function get_class;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use function register_shutdown_function;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function var_export;
use const JSON_ERROR_NONE;

class Async extends CodeceptionModule
{
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
    }

    private $slaveControllerInputFilenames = [];

    private $slaveControllerOutputFilenames = [];

    private function addProcess()
    {
        $handle = tempnam(sys_get_temp_dir(), 'codecept_async');
        register_shutdown_function('unlink', $handle);

        $this->slaveControllerInputFilenames[$handle] = tempnam(sys_get_temp_dir(), 'codecept_async_input');
        register_shutdown_function('unlink', $this->slaveControllerInputFilenames[$handle]);

        $this->slaveControllerOutputFilenames[$handle] = tempnam(sys_get_temp_dir(), 'codecept_async_output');
        register_shutdown_function('unlink', $this->slaveControllerOutputFilenames[$handle]);

        return $handle;
    }

    /**
     * @param $inputFilename
     * @param $outputFilename
     * @param string $filename
     * @param string $class
     * @param string $method
     * @param array $params
     */
    public static function _bootstrapAsyncMethod($inputFilename, $outputFilename, $filename, $class, $method, $params)
    {
        self::$slaveControllerInputFilename = $inputFilename;
        self::$slaveControllerOutputFilename = $outputFilename;
        $returnValue = call_user_func_array([$class, $method], $params);
        $serializedReturnValue = self::serialize($returnValue);
        file_put_contents($filename, $serializedReturnValue);
    }

    private function getAutoloadPath()
    {
        return $this->config['autoload_path'];
    }

    private function generateCode($handle, $file, $class, $method, array $params)
    {
        return sprintf(
            "<?php\nrequire %s;\nrequire %s;\n%s::_bootstrapAsyncMethod(%s, %s, %s, %s, %s, %s);",
            var_export($this->getAutoloadPath(), true),
            var_export($file, true),
            __CLASS__,
            var_export($this->slaveControllerInputFilenames[$handle], true),
            var_export($this->slaveControllerOutputFilenames[$handle], true),
            var_export($handle, true),
            var_export($class, true),
            var_export($method, true),
            var_export($params, true)
        );
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

    public function seeAsyncMethodFinished($handle)
    {
        assert(isset($this->processes[$handle]));
        $process = $this->processes[$handle];
        $this->assertTrue($process->isTerminated());
    }

    public function seeAsyncMethodFinishedSuccessfully($handle)
    {
        assert(isset($this->processes[$handle]));
        $process = $this->processes[$handle];
        $this->assertTrue($process->isTerminated() && $process->isSuccessful());
    }

    private function getProcess($handle)
    {
        assert(isset($this->processes[$handle]));
        return $this->processes[$handle];
    }

    private function getFinishedProcess($handle)
    {
        $process = $this->getProcess($handle);

        if ($process->isRunning()) {
            $process->wait();
        }

        return $process;
    }

    public function grabAsyncMethodStatusCode($handle)
    {
        return $this->getFinishedProcess($handle)->getExitCode();
    }

    public function grabAsyncMethodOutput($handle)
    {
        return $this->getFinishedProcess($handle)->getOutput();
    }

    public function grabAsyncMethodErrorOutputSoFar($handle)
    {
        return $this->getProcess($handle)->getErrorOutput();
    }

    public function grabAsyncMethodErrorOutput($handle)
    {
        return $this->getFinishedProcess($handle)->getErrorOutput();
    }

    /**
     * @param $handle
     * @return mixed
     * @throws Exception
     */
    public function grabAsyncMethodReturnValue($handle)
    {
        assert(0 === $this->getFinishedProcess($handle)->getExitCode());
        return self::deserialize(file_get_contents($handle));
    }

    public function haveAllAsyncMethodsFinished()
    {
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                $process->wait();
            }
        }
    }

    /**
     * @param $data
     * @return false|string
     */
    private static function serialize($data)
    {
        return json_encode($data);
    }

    /**
     * @param $string
     * @return mixed
     * @throws Exception
     */
    private static function deserialize($string)
    {
        $data = json_decode($string, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Deserialization failed due to JSON decoding error: ' . json_last_error_msg());
        }
        return $data;
    }

    private static $slaveControllerInputFilename;

    private static $slaveControllerOutputFilename;

    public static function getSlaveController()
    {
        return new SlaveController(self::$slaveControllerInputFilename, self::$slaveControllerOutputFilename);
    }

    public function getMasterController($handle)
    {
        return new MasterController(
            $this->slaveControllerOutputFilenames[$handle],
            $this->slaveControllerInputFilenames[$handle]
        );
    }
}
