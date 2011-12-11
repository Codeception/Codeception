<?php
namespace Codeception\TestCase;

class Cept extends \Codeception\TestCase
{

    public function loadScenario()
    {
        $scenario = $this->scenario;
        require_once $this->testfile;
    }

}
