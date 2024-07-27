<?php

declare(strict_types=1);

namespace Codeception\Step;

use Codeception\Exception\ConditionalAssertionFailed;
use Codeception\Lib\ModuleContainer;
use Codeception\Util\Template;
use PHPUnit\Framework\AssertionFailedError;

use function preg_replace;
use function str_replace;
use function ucfirst;

class ConditionalAssertion extends Assertion implements GeneratedStep
{
    public function run(?ModuleContainer $container = null): void
    {
        try {
            parent::run($container);
        } catch (AssertionFailedError $e) {
            throw new ConditionalAssertionFailed($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getAction(): string
    {
        $action = 'can' . ucfirst($this->action);
        return (string)preg_replace('#^canDont#', 'cant', $action);
    }

    public function getHumanizedAction(): string
    {
        return $this->humanize($this->action . ' ' . $this->getHumanizedArguments());
    }

    public static function getTemplate(Template $template): ?Template
    {
        $action = $template->getVar('action');

        if ((!str_starts_with($action, 'see')) && (!str_starts_with($action, 'dontSee'))) {
            return null;
        }

        $conditionalDoc = "* [!] Conditional Assertion: Test won't be stopped on fail\n     " . $template->getVar('doc');

        $prefix = 'can';
        if (str_starts_with($action, 'dontSee')) {
            $prefix = 'cant';
            $action = str_replace('dont', '', $action);
        }

        return $template
            ->place('doc', $conditionalDoc)
            ->place('action', $prefix . ucfirst($action))
            ->place('step', 'ConditionalAssertion');
    }

    public function match(string $name): bool
    {
        return str_starts_with($name, 'see') || str_starts_with($name, 'dontSee');
    }
}
