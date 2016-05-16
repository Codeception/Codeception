<?php
namespace CliGuy;

class GeneratorSteps extends \CliGuy
{
    public function seeFileWithGeneratedClass($class, $path = '')
    {
        $I = $this;
        $I->seeFileFound($class.'.php', $path);
        $I->seeInThisFile('class '.$class);
    }

    public function seeAutoloaderWasAdded($prefix, $path)
    {
        $I = $this;
        $I->seeFileFound('_bootstrap.php', $path);
        $I->seeInThisFile("\\Codeception\\Util\\Autoload::addNamespace('$prefix', __DIR__.DIRECTORY_SEPARATOR.");
    }
}
