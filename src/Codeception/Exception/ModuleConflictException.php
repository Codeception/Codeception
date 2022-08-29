<?php

declare(strict_types=1);

namespace Codeception\Exception;

use Exception;

use function is_object;
use function ltrim;
use function str_replace;

class ModuleConflictException extends Exception
{
    /**
     * ModuleConflictException constructor.
     *
     * @param object|string $module
     * @param object|string $conflicted
     */
    public function __construct($module, $conflicted, string $additional = '')
    {
        if (is_object($module)) {
            $module = $module::class;
        }
        if (is_object($conflicted)) {
            $conflicted = $conflicted::class;
        }
        $module = ltrim(str_replace('Codeception\Module\\', '', $module), '\\');
        $conflicted = ltrim(str_replace('Codeception\Module\\', '', $conflicted), '\\');
        $this->message = "{$module} module conflicts with {$conflicted}\n\n--\n"
            . "This usually happens when you enable two modules with the same actions but with different backends.\n"
            . "For instance, you can't run PhpBrowser, WebDriver, Laravel5 modules in one suite,\n"
            . "as they implement similar methods but use different drivers to execute them.\n"
            . "You can load a part of module (like: ORM) to avoid conflict.\n"
            . $additional;
    }
}
