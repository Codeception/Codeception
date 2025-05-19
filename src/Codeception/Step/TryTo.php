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
        $action = (string) $template->getVar('action');

        if (
            str_starts_with($action, 'have') ||
            str_starts_with($action, 'am')   ||
            str_starts_with($action, 'wait') ||
            str_starts_with($action, 'grab')
        ) {
            return null;
        }

        $conditionalDoc = "* [!] Test won't be stopped on fail. Error won't be logged \n     "
            . $template->getVar('doc');

        return $template
            ->place('doc', $conditionalDoc)
            ->place('action', 'tryTo' . ucfirst($action))
            ->place('return', 'return ')
            ->place('return_type', ': bool')
            ->place('step', 'TryTo');
    }
}
