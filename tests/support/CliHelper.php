<?php
namespace Codeception\Module;

// here you can define custom functions for CliGuy 

class CliHelper extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        $this->debug('Building actor classes for claypit');
        $this->getModule('Cli')->runShellCommand('php ' . codecept_root_dir() . 'codecept build -c ' . codecept_data_dir() . 'claypit');
    }

    public function _before(\Codeception\TestInterface $test)
    {
        codecept_debug('creating dirs');
        $this->getModule('Filesystem')->copyDir(codecept_data_dir() . 'claypit', codecept_data_dir() . 'sandbox');
    }

    public function _after(\Codeception\TestInterface $test)
    {
        codecept_debug('deleting dirs');
        $this->getModule('Filesystem')->deleteDir(codecept_data_dir() . 'sandbox');
        chdir(\Codeception\Configuration::projectDir());
    }

    public function executeCommand($command, $fail = true)
    {
        $this->getModule('Cli')->runShellCommand('php ' . \Codeception\Configuration::projectDir() . 'codecept ' . $command . ' -n', $fail);
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
