<?php
namespace Codeception\Test\Loader;

use Codeception\Lib\Parser;
use Codeception\Test\Cept as CeptFormat;

class Cept implements LoaderInterface
{
    protected $tests = [];

    public function getPattern()
    {
        return '~Cept\.php$~';
    }

    function loadTests($file)
    {
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
