<?php
namespace TestFramework\Module\src\Module\Executor;

abstract class AbstractExecutor implements ExecutorInterface
{
    protected $config = [];
    protected $PID = '';

    public function __construct(array $config)
    {
        $this->config = $config;
    }
}
