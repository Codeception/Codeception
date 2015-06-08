<?php
namespace Codeception\Step;

use Codeception\Exception\ConditionalAssertionFailed;
use Codeception\Lib\ModuleContainer;

class ConditionalAssertion extends Assertion
{
    public function run(ModuleContainer $container = null)
    {
        try {
            parent::run($container);
        } catch (\PHPUnit_Framework_AssertionFailedError $e) {
            throw new ConditionalAssertionFailed($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getAction()
    {
        return 'can' . ucfirst($this->action);
    }

    public function getHumanizedAction()
    {
        return $this->humanize($this->action . ' ' . $this->getHumanizedArguments());
    }
}
