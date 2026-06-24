<?php

declare(strict_types=1);

namespace Tests\Support\Step;

class GeneratorSteps extends \Tests\Support\CliTester
{
    public function seeFileWithGeneratedClass(string $class, string $path = '')
    {
        $I = $this;
        $I->seeFileFound($class . '.php', $path);
        $I->seeInThisFile('class ' . $class);
    }
}
