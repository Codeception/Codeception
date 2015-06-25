<?php 
namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Step as CodeceptionStep;

class Meta extends CodeceptionStep
{
    protected function storeCallerInfo()
    {
    }

    public function run(ModuleContainer $container = null)
    {
    }

    public function setTraceInfo($file, $line)
    {
        $this->file = $file;
        $this->line = $line;
    }

    public function setActor($actor)
    {
        $this->actor = $actor;
    }
}
