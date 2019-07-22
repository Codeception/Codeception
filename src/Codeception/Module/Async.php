<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleException;
use Codeception\Module as CodeceptionModule;
use Codeception\Test\Cest;
use Codeception\TestInterface;
use Exception;
use Symfony\Component\Process\PhpProcess;
use Symfony\Component\VarExporter\Exception\ExceptionInterface;
use Symfony\Component\VarExporter\VarExporter;
use function assert;
use function call_user_func_array;
use function file_get_contents;
use function register_shutdown_function;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;

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
     * @throws ExceptionInterface
     * @throws ModuleException
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

        try {
            $serializedParams = VarExporter::export($params);
        } catch (ExceptionInterface $e) {
            throw new ModuleException($this, 'Failed serializing parameters: ' . $e->getMessage());
        }

        $code = sprintf(
            "<?php\nrequire %s;\nrequire %s;\n%s::_bootstrapAsyncMethod(%s, %s, %s, %s);",
            VarExporter::export(__DIR__ . '/../../../vendor/autoload.php'),
            VarExporter::export($currentTest->getFileName()),
            __CLASS__,
            VarExporter::export($handle),
            VarExporter::export($currentTest->getTestClass()),
            VarExporter::export($methodName),
            $serializedParams
        );

        $this->debug($code);

        register_shutdown_function('unlink', $handle);

        $process = new PhpProcess($code);

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

    private function getFinishedProcess($handle)
    {
        assert(isset($this->processes[$handle]));

        $process = $this->processes[$handle];

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

    public function grabAsyncMethodErrorOutput($handle)
    {
        return $this->getFinishedProcess($handle)->getErrorOutput();
    }

    public function grabAsyncMethodReturnValue($handle)
    {
        assert(0 === $this->getFinishedProcess($handle)->getExitCode());
        return eval('return ' . file_get_contents($handle) . ';');
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
     * @throws ExceptionInterface
     */
    public static function _bootstrapAsyncMethod($filename, $class, $method, $params)
    {
        $returnValue = call_user_func_array([$class, $method], $params);
        $serializedReturnValue = VarExporter::export($returnValue);
        file_put_contents($filename, $serializedReturnValue);
    }
}
