<?php
namespace Codeception\Step;

use Codeception\Exception\ConditionalAssertionFailed;
use Codeception\Lib\ModuleContainer;
use Codeception\Util\Template;

class ConditionalAssertion extends Assertion implements GeneratedStep
{
    public function run(ModuleContainer $container = null)
    {
        try {
            parent::run($container);
        } catch (\PHPUnit\Framework\AssertionFailedError $e) {
            throw new ConditionalAssertionFailed($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getAction()
    {
        $action = 'can' . ucfirst($this->action);
        $action = preg_replace('/^canDont/', 'cant', $action);
        return $action;
    }

    public function getHumanizedAction()
    {
        return $this->humanize($this->action . ' ' . $this->getHumanizedArguments());
    }

    public static function getTemplate(Template $template)
    {
        $action = $template->getVar('action');

        if ((0 !== strpos($action, 'see')) && (0 !== strpos($action, 'dontSee'))) {
            return '';
        }

        $conditionalDoc = "* [!] Conditional Assertion: Test won't be stopped on fail\n     " . $template->getVar('doc');

        $prefix = 'can';
        if (strpos($action, 'dontSee') === 0) {
            $prefix = 'cant';
            $action = str_replace('dont', '', $action);
        }

        return $template
            ->place('doc', $conditionalDoc)
            ->place('action', $prefix . ucfirst($action))
            ->place('step', 'ConditionalAssertion');
    }

    public function match($name)
    {
        return 0 === strpos($name, 'see') || 0 === strpos($name, 'dontSee');
    }
}
