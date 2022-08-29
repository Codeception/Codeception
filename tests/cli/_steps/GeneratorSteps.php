<?php

declare(strict_types=1);

namespace CliGuy;

class GeneratorSteps extends \CliGuy
{
    public function seeFileWithGeneratedClass(string $class, string $path = '')
    {
        $I = $this;
        $I->seeFileFound($class . '.php', $path);
        $I->seeInThisFile('class ' . $class);
    }
}
