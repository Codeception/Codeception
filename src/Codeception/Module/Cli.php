<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\TestInterface;

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
class Cli extends CodeceptionModule
{
    public $output = '';

    public $result = null;

    public function _before(TestInterface $test)
    {
        $this->output = '';
    }

    /**
     * Executes a shell command.
     * Fails If exit code is > 0. You can disable this by setting second parameter to false
     *
     * ```php
     * <?php
     * $I->runShellCommand('phpunit');
     *
     * // do not fail test when command fails
     * $I->runShellCommand('phpunit', false);
     * ```
     *
     * @param $command
     * @param bool $failNonZero
     */
    public function runShellCommand($command, $failNonZero = true)
    {
        $data = [];
        exec("$command", $data, $resultCode);
        $this->result = $resultCode;
        $this->output = implode("\n", $data);
        if ($this->output === null) {
            \PHPUnit\Framework\Assert::fail("$command can't be executed");
        }
        if ($resultCode !== 0 && $failNonZero) {
            \PHPUnit\Framework\Assert::fail("Result code was $resultCode.\n\n" . $this->output);
        }
        $this->debug(preg_replace('~s/\e\[\d+(?>(;\d+)*)m//g~', '', $this->output));
    }

    /**
     * Checks that output from last executed command contains text
     *
     * @param $text
     */
    public function seeInShellOutput($text)
    {
        \PHPUnit\Framework\Assert::assertContains($text, $this->output);
    }

    /**
     * Checks that output from latest command doesn't contain text
     *
     * @param $text
     *
     */
    public function dontSeeInShellOutput($text)
    {
        $this->debug($this->output);
        \PHPUnit\Framework\Assert::assertNotContains($text, $this->output);
    }

    /**
     * @param $regex
     */
    public function seeShellOutputMatches($regex)
    {
        \PHPUnit\Framework\Assert::assertRegExp($regex, $this->output);
    }

    /**
     * Checks result code
     *
     * ```php
     * <?php
     * $I->seeResultCodeIs(0);
     * ```
     *
     * @param $code
     */
    public function seeResultCodeIs($code)
    {
        $this->assertEquals($this->result, $code, "result code is $code");
    }

    /**
     * Checks result code
     *
     * ```php
     * <?php
     * $I->seeResultCodeIsNot(0);
     * ```
     *
     * @param $code
     */
    public function seeResultCodeIsNot($code)
    {
        $this->assertNotEquals($this->result, $code, "result code is $code");
    }
}
