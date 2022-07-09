<?php

namespace Codeception\Module;

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
        $this->getModule('Filesystem')->amInPath(codecept_root_dir());
    }

    public function executeCommand($command, bool $fail = true, $phpOptions = '')
    {
        $this->getModule('Cli')->runShellCommand('php ' . $phpOptions . ' ' . \Codeception\Configuration::projectDir() . 'codecept ' . $command . ' -n', $fail);
    }

    public function executeFailCommand($command)
    {
        $this->getModule('Cli')->runShellCommand('php ' . \Codeception\Configuration::projectDir() . 'codecept ' . $command . ' -n', false);
    }

    /**
     * @return string
     */
    public function grabFromOutput($regex)
    {
        $match = [];
        $found = preg_match($regex, $this->getModule('Cli')->output, $match);
        if (!$found) {
            return '';
        }

        return $match[1];
    }

    public function seeDirFound($dir)
    {
        $this->assertTrue(is_dir($dir) && file_exists($dir), "Directory does not exist");
    }
}
