<?php
namespace Codeception\Module;

class Cli extends \Codeception\Module
{
    protected $output = '';

    public function _cleanup()
    {
        $this->output = '';
    }
    
    public function amInPath($dir) {
        chdir($dir);
    }

    public function runShellCommmand($command) {
        $this->output = shell_exec("$command");
        if ($this->output === null) throw new \RuntimeException("$command can't be executed");
        $this->debug($this->output);
    }

    public function seeInShellOutput($text) {

        \PHPUnit_Framework_Assert::assertContains($text, $this->output);
    }

    public function dontSeeInShellOutput($text) {
        $this->debug($this->output);
        \PHPUnit_Framework_Assert::assertNotContains($text, $this->output);
    }

}