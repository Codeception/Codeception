<?php
namespace TestFramework\Module\src\Module;

use TestFramework\Module\src\Module\Executor\ExecutorInterface;
use TestFramework\Module\src\Module\Source\PathInterface;
use TestFramework\Module\src\Module\Task\TaskInterface;

class Module
{
    /** @var PathInterface */
    private $path;
    /** @var ExecutorInterface */
    private $executor;
    private $name = 'Unknown module';

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param PathInterface $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param ExecutorInterface $executor
     * @return $this
     */
    public function setExecutor(ExecutorInterface $executor)
    {
        $this->executor = $executor;
        return $this;
    }

    public function start()
    {
        if (null === $this->executor) {
            return;
        }
        echo $this->name . ': ';
        $pid = $this->executor->start($this->path);
        echo ' with PID: ' . $pid . PHP_EOL;
    }

    public function restart()
    {
        if (null === $this->executor) {
            return;
        }
        $this->executor->restart($this->path);
    }

    public function kill()
    {
        if (null === $this->executor) {
            return;
        }
        echo $this->name . ': ';
        $pid = $this->executor->kill();
        echo ' for PID: ' . $pid . PHP_EOL;
    }
}
