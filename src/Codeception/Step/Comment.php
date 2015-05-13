<?php
namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;

class Comment extends \Codeception\Step
{

    public function __toString()
    {
        return $this->getAction();
    }

    public function getHumanizedAction()
    {
        return $this->getAction();
    }

    public function getHtml()
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
