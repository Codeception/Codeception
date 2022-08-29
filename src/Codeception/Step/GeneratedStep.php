<?php

namespace Codeception\Step;

use Codeception\Util\Template;

interface GeneratedStep
{
    public static function getTemplate(Template $template): ?Template;
}
