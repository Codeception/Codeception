<?php

namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\Test\Cest;
use Codeception\TestInterface;
use Exception;
use Symfony\Component\Process\PhpProcess;
use function assert;
use function call_user_func_array;
use function file_get_contents;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use function register_shutdown_function;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use const JSON_ERROR_NONE;

class Async extends CodeceptionModule
{
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

        $handle = tempnam(sys_get_temp_dir(), 'codecept_async');

        $this->debug($handle);

        $code = sprintf(
            "<?php\nrequire %s;\nrequire %s;\n%s::_bootstrapAsyncMethod(%s, %s, %s, %s);",
            var_export(__DIR__ . '/../../../vendor/autoload.php', true),
            var_export($currentTest->getFileName(), true),
            __CLASS__,
            var_export($handle, true),
            var_export($currentTest->getTestClass(), true),
            var_export($methodName, true),
            var_export($params, true)
        );

        $this->debug($code);

        register_shutdown_function('unlink', $handle);

        $process = new PhpProcess(
            $code,
            null,
            null,
            3
        );

        $this->processes[$handle] = $process;

        $process->start(function ($type, $data) use ($methodName, $currentTest) {
            $this->debug(
                sprintf(
                    '%s::%s [%s] %s',
                    $currentTest->getTestClass(),
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
     * @param string $filename
     * @param string $class
     * @param string $method
     * @param array $params
     */
    public static function _bootstrapAsyncMethod($filename, $class, $method, $params)
    {
        $returnValue = call_user_func_array([$class, $method], $params);
        $serializedReturnValue = self::serialize($returnValue);
        file_put_contents($filename, $serializedReturnValue);
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
}
