<?php
namespace Codeception\Coverage;

class DummyCodeCoverage extends \PHP_CodeCoverage
{
    public function start($id, $clear = FALSE)
    {

    }

    function stop($append = TRUE)
    {

    }
}
