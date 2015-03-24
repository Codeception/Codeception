<?php
namespace Codeception\Module;

// here you can define custom functions for CliGuy 

class CliHelper extends \Codeception\Module
{
    public function _before(\Codeception\TestCase $test) {
        $this->getModule('Filesystem')->copyDir(\Codeception\Configuration::dataDir().'claypit', \Codeception\Configuration::dataDir().'sandbox');
    }

    public function _after(\Codeception\TestCase $test) {
        $this->getModule('Filesystem')->deleteDir(\Codeception\Configuration::dataDir().'sandbox');
        chdir(\Codeception\Configuration::projectDir());
    }

    public function executeCommand($command) {
        $this->getModule('Cli')->runShellCommand('php '.\Codeception\Configuration::projectDir().'codecept '.$command);
    }

    public function seeDirFound($dir)
    {
        $this->assertTrue(is_dir($dir) && file_exists($dir), "Directory does not exist");
    }
}
