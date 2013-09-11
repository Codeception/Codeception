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

    public function seeAutoloaderWasAdded($suffix, $path)
    {
        $I = $this;
        $I->seeFileFound('_bootstrap.php',$path);
        $I->seeInThisFile("\\Codeception\\Util\\Autoload::registerSuffix('$suffix', __DIR__.DIRECTORY_SEPARATOR.");
    }

}