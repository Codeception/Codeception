<?php
namespace Codeception\Coverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;

class DummyCodeCoverage extends CodeCoverage
{
    public function start($id, $clear = false)
    {
    }

    public function stop($append = true, $linesToBeCovered = [], array $linesToBeUsed = [], $ignoreForceCoversAnnotation = false)
    {
    }
}
