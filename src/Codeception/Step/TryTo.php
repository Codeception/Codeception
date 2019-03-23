<?php

namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Util\Template;

class TryTo extends Assertion implements GeneratedStep
{
    public function run(ModuleContainer $container = null)
    {
        try {
            parent::run($container);
        } catch (\Exception $e) {
            codecept_debug("Failed to perform: {$e->getMessage()}, skipping...");
        }
    }

    public static function getTemplate(Template $template)
    {
        $action = $template->getVar('action');

        if ((strpos($action, 'have') === 0) || (strpos($action, 'am') === 0)) {
            return; // dont try on conditions
        }

        if ((strpos($action, 'see') === 0) || (strpos($action, 'dontSee') === 0)) {
            return; // dont try on assertions
        }

        if (strpos($action, 'wait') === 0) {
            return; // dont try on waiters
        }

        $conditionalDoc = "* [!] Test won't be stopped on fail. Error won't be logged \n     " . $template->getVar('doc');

        return $template
            ->place('doc', $conditionalDoc)
            ->place('action', 'tryTo' . ucfirst($action))
            ->place('step', 'ConditionalAssertion');
    }
}
