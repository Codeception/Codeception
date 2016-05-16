<?php
namespace Codeception\Coverage;

class DummyCodeCoverage extends \PHP_CodeCoverage
{
    public function start($id, $clear = false)
    {
    }

    public function stop($append = true, $linesToBeCovered = [], array $linesToBeUsed = [])
    {
    }
}
