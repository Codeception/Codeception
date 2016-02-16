<?php
namespace Codeception\Module;

// here you can define custom functions for CliGuy 

class CliHelper extends \Codeception\Module
{
    public function _before(\Codeception\TestInterface $test)
    {
        codecept_debug('creating dirs');
        $this->getModule('Filesystem')->copyDir(\Codeception\Configuration::dataDir().'claypit', \Codeception\Configuration::dataDir().'sandbox');
    }

    public function _after(\Codeception\TestInterface $test)
    {
        codecept_debug('deleting dirs');
        $this->getModule('Filesystem')->deleteDir(\Codeception\Configuration::dataDir().'sandbox');
        chdir(\Codeception\Configuration::projectDir());
    }

    public function executeCommand($command)
    {
        $this->getModule('Cli')->runShellCommand('php '.\Codeception\Configuration::projectDir().'codecept '.$command.' -n');
    }

    public function executeFailCommand($command)
    {
        $this->getModule('Cli')->runShellCommand('php '.\Codeception\Configuration::projectDir().'codecept '.$command.' -n', false);
    }

    public function seeDirFound($dir)
    {
        $this->assertTrue(is_dir($dir) && file_exists($dir), "Directory does not exist");
    }
}
