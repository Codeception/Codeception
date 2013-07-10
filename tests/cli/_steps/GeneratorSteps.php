<?php
namespace CliGuy;

class GeneratorSteps extends \CliGuy
{
    function seeFileWithGeneratedClass($class, $path = '')
    {
        $I = $this;
        $I->seeFileFound($class.'.php', $path);
        $I->seeInThisFile('class '.$class);

    }

}