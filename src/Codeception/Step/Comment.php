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

    public function getHtmlAction()
    {
        return '<strong>' . $this->getAction() . '</strong>';
    }

    public function run(ModuleContainer $container = null)
    {
        // don't do anything, let's rest
    }

}
