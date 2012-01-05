<?php
namespace Codeception\TestCase;

class Cept extends \Codeception\TestCase
{

    public function loadScenario()
    {
        if (file_exists($this->bootstrap)) require $this->bootstrap;
        $scenario = $this->scenario;
        require_once $this->testfile;
    }

}
