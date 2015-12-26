<?php
namespace Codeception\TestCase\Loader;

use Codeception\Lib\Parser;
use Codeception\TestCase\Cept as CeptFormat;

class Cept implements Loader
{
    protected $tests = [];

    public function getPattern()
    {
        return '~Cept\.php$~';
    }

    function loadTests($file) {
        Parser::validate($file);
        $name = basename($file,'Cept.php');

        $cept = new CeptFormat();
        $cept->configName($name)
            ->configFile($file);
        $this->tests[] = $cept;
    }

    public function getTests()
    {
        return $this->tests;
    }
}