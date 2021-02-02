<?php

declare(strict_types=1);

namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Util\Template;
use Exception;
use function codecept_debug;
use function strpos;
use function ucfirst;

class TryTo extends Assertion implements GeneratedStep
{
    public function run(ModuleContainer $container = null): bool
    {
        $this->isTry = true;
        try {
            parent::run($container);
        } catch (Exception $e) {
            codecept_debug("Failed to perform: {$e->getMessage()}, skipping...");
            return false;
        }
        return true;
    }

    public static function getTemplate(Template $template): ?Template
    {
        $action = $template->getVar('action');

        if ((strpos($action, 'have') === 0) || (strpos($action, 'am') === 0)) {
            return null; // dont try on conditions
        }

        if (strpos($action, 'wait') === 0) {
            return null; // dont try on waiters
        }

        if (strpos($action, 'grab') === 0) {
            return null; // dont on grabbers
        }

        $conditionalDoc = "* [!] Test won't be stopped on fail. Error won't be logged \n     " . $template->getVar('doc');

        return $template
            ->place('doc', $conditionalDoc)
            ->place('action', 'tryTo' . ucfirst($action))
            ->place('step', 'TryTo');
    }
}
