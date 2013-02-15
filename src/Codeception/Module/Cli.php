<?php
namespace Codeception\Module;

/**
 * Wrapper for basic shell commands and shell output
 *
 * ## Responsibility
 * * Maintainer: **davert**
 * * Status: **stable**
 * * Contact: codecept@davert.mail.ua
 *
 * *Please review the code of non-stable modules and provide patches if you have issues.*
 */
class Cli extends \Codeception\Module
{
    protected $output = '';

    public function _cleanup()
    {
        $this->output = '';
    }

    /**
     * Executes a shell command
     *
     * @param $command
     */
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

    /**
     * Checks that output from last executed command contains text
     *
     * @param $text
     */
    public function seeInShellOutput($text) {

        \PHPUnit_Framework_Assert::assertContains($text, $this->output);
    }

    /**
     * Checks that output from latest command doesn't contain text
     *
     * @param $text
     *
     */
    public function dontSeeInShellOutput($text) {
        $this->debug($this->output);
        \PHPUnit_Framework_Assert::assertNotContains($text, $this->output);
    }

}