<?php

declare(strict_types=1);

namespace Codeception\Exception;

use Exception;

use function is_object;
use function ltrim;
use function str_replace;

class ModuleException extends Exception
{
    protected string $module;

    /**
     * ModuleException constructor.
     *
     * @param object|string $module
     */
    public function __construct($module, string $message)
    {
        if (is_object($module)) {
            $module = $module::class;
        }
        $module = ltrim(str_replace('Codeception\Module\\', '', $module), '\\');
        $this->module = $module;
        parent::__construct($message);
        $this->message = "{$module}: {$this->message}";
    }
}
