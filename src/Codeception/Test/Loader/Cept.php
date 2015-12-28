<?php
namespace Codeception\Test\Loader;

use Codeception\Lib\Parser;
use Codeception\Test\Format\Cept as CeptFormat;

class Cept implements Loader
{
    protected $tests = [];

    public function getPattern()
    {
        return '~Cept\.php$~';
    }

    function loadTests($file) {
        Parser::validate($file);
        $name = basename($file, 'Cept.php');

        $cept = new CeptFormat($name, $file);
        $this->tests[] = $cept;
    }

    public function getTests()
    {
        return $this->tests;
    }
}