<?php
namespace TestFramework\Module\src\Module\Executor;

use TestFramework\Module\src\Module\Source\PathInterface;

interface ExecutorInterface
{
    public function start(PathInterface $path);

    public function kill();
    
    public function restart(PathInterface $path);
}
