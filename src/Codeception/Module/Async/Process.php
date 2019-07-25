<?php

namespace Codeception\Module\Async;

use Codeception\Module\Async;
use Exception;
use Symfony\Component\Process\PhpProcess;

class Process
{
    /**
     * @var string
     */
    private $handle;

    /**
     * @var PhpProcess
     */
    private $process;

    /**
     * @var IPC
     */
    private $ipc;

    /**
     * @var bool
     */
    private $returnValueRetrieved = false;

    /**
     * @var mixed
     */
    private $returnValue;

    /**
     * @param string $handle
     * @param PhpProcess $process
     * @param IPC $ipc
     */
    public function __construct($handle, $process, $ipc)
    {
        $this->handle = $handle;
        $this->process = $process;
        $this->ipc = $ipc;
    }

    /**
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @return PhpProcess
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @return IPC
     */
    public function getIpc()
    {
        return $this->ipc;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getReturnValue()
    {
        if (!$this->returnValueRetrieved) {
            $this->returnValueRetrieved = true;
            $this->returnValue = $this->ipc->read(Async::RESULT_CHANNEL);
        }
        return $this->returnValue;
    }
}
