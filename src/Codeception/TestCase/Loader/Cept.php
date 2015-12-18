<?php
namespace Codeception\TestCase\Loader;

use Codeception\Lib\Parser;
use Codeception\TestCase\Cept as CeptFormat;

class Cept implements Loader
{
    protected $cept;

    public function getPattern()
    {
        return '~Cept\.php$~';
    }

    function loadTests($file) {
        Parser::validate($file);
        $name = basename($file,'Cept.php');

        $this->cept = new CeptFormat();
        $this->cept->configName($name)
            ->configFile($file);
    }

    public function getTests()
    {
        return [$this->cept];
    }
}