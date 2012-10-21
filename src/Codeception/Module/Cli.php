<?php
namespace Codeception\Module;

class Cli extends \Codeception\Module
{
    protected $output = '';

    public function _cleanup()
    {
        $this->output = '';
    }
    
    public function runShellCommmand($command) {
        $data = array();
        exec("$command", $data, $resultCode);
        $this->output = implode("\n", $data);
        if ($this->output === null) \PHPUnit_Framework_Assert::fail("$command can't be executed");
        if ($resultCode !== 0) {
            \PHPUnit_Framework_Assert::fail("Result code was $resultCode.\n\n".$this->output);
        }
        $this->debug(preg_replace('~s/\e\[\d+(?>(;\d+)*)m//g~', '',$this->output));
    }

    public function seeInShellOutput($text) {

        \PHPUnit_Framework_Assert::assertContains($text, $this->output);
    }

    public function dontSeeInShellOutput($text) {
        $this->debug($this->output);
        \PHPUnit_Framework_Assert::assertNotContains($text, $this->output);
    }

}