<?php
namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Step as CodeceptionStep;

class Comment extends CodeceptionStep
{
    public function __toString()
    {
        return $this->getAction();
    }

    public function getHumanizedAction()
    {
        return $this->getAction();
    }

    public function getHtml($highlightColor = '#732E81')
    {
        return '<strong>' . $this->getAction() . '</strong>';
    }

    public function getPhpCode()
    {
        return '// ' . $this->getAction();
    }

    public function run(ModuleContainer $container = null)
    {
        // don't do anything, let's rest
    }
}
