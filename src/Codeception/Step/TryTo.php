<?php

declare(strict_types=1);

namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Util\Template;
use Exception;

use function codecept_debug;
use function ucfirst;

class TryTo extends Assertion implements GeneratedStep
{
    public function run(?ModuleContainer $container = null): bool
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

        if ((str_starts_with($action, 'have')) || (str_starts_with($action, 'am'))) {
            return null; // dont try on conditions
        }

        if (str_starts_with($action, 'wait')) {
            return null; // dont try on waiters
        }

        if (str_starts_with($action, 'grab')) {
            return null; // dont on grabbers
        }

        $conditionalDoc = "* [!] Test won't be stopped on fail. Error won't be logged \n     " . $template->getVar('doc');

        return $template
            ->place('doc', $conditionalDoc)
            ->place('action', 'tryTo' . ucfirst($action))
            ->place('return', 'return ')
            ->place('return_type', ': bool')
            ->place('step', 'TryTo');
    }
}
