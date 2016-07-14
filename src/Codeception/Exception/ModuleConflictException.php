<?php
namespace Codeception\Exception;

class ModuleConflictException extends \Exception
{
    public function __construct($module, $conflicted, $additional = '')
    {
        if (is_object($module)) {
            $module = get_class($module);
        }
        if (is_object($conflicted)) {
            $conflicted = get_class($conflicted);
        }
        $module = ltrim(str_replace('Codeception\Module\\', '', $module), '\\');
        $conflicted = ltrim(str_replace('Codeception\Module\\', '', $conflicted), '\\');
        $this->message = "$module module conflicts with $conflicted\n\n--\n"
            . "This usually happens when you enable two modules with the same actions but with different backends.\n"
            . "For instance, you can't run PhpBrowser, WebDriver, Laravel5 modules in one suite,\n"
            . "as they implement similar methods but use different drivers to execute them.\n"
            . "You can load a part of module (like: ORM) to avoid conflict.\n"
            . $additional;
    }
}
