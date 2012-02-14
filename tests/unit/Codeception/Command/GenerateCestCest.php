<?php
namespace Codeception\Command;

class GenerateCestCest
{
    public $class = 'Codeception\Command\GenerateCest';
    
    public function _before() {
        require_once \Codeception\Configuration::dataDir().'DummyClass.php';
    }

    // Test for GenerateCest.execute
    public function execute(\CodeGuy $I) {



    }
}
