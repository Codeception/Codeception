<?php
namespace Codeception\Coverage;

class DummyCodeCoverage extends \PHP_CodeCoverage
{
    public function start($id, $clear = false)
    {

    }

    function stop($append = true, $linesToBeCovered = [], array $linesToBeUsed = [])
    {

    }
}
