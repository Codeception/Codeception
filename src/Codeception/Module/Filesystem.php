<?php
namespace Codeception\Module;

use Codeception\Util\FileSystem as Util;
use Symfony\Component\Finder\Finder;
use Codeception\Module as CodeceptionModule;
use Codeception\TestInterface;
use Codeception\Configuration;

/**
 * Module for testing local filesystem.
 * Fork it to extend the module for FTP, Amazon S3, others.
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: codecept@davert.mail.ua
 *
 * Module was developed to test Codeception itself.
 */
class Filesystem extends CodeceptionModule
{
    protected $file = null;
    protected $filepath = null;

    protected $path = '';

    public function _before(TestInterface $test)
    {
        $this->path = Configuration::projectDir();
    }

    /**
     * Enters a directory In local filesystem.
     * Project root directory is used by default
     *
     * @param $path
     */
    public function amInPath($path)
    {
        chdir($this->path = $this->absolutizePath($path) . DIRECTORY_SEPARATOR);
        $this->debug('Moved to ' . getcwd());
    }

    protected function absolutizePath($path)
    {
        // *nix way
        if (strpos($path, '/') === 0) {
            return $path;
        }
        // windows
        if (strpos($path, ':\\') === 1) {
            return $path;
        }

        return $this->path . $path;
    }

    /**
     * Opens a file and stores it's content.
     *
     * Usage:
     *
     * ``` php
     * <?php
     * $I->openFile('composer.json');
     * $I->seeInThisFile('codeception/codeception');
     * ?>
     * ```
     *
     * @param $filename
     */
    public function openFile($filename)
    {
        $this->file = file_get_contents($this->absolutizePath($filename));
    }

    /**
     * Deletes a file
     *
     * ``` php
     * <?php
     * $I->deleteFile('composer.lock');
     * ?>
     * ```
     *
     * @param $filename
     */
    public function deleteFile($filename)
    {
        if (!file_exists($this->absolutizePath($filename))) {
            \PHPUnit_Framework_Assert::fail('file not found');
        }
        unlink($this->absolutizePath($filename));
    }

    /**
     * Deletes directory with all subdirectories
     *
     * ``` php
     * <?php
     * $I->deleteDir('vendor');
     * ?>
     * ```
     *
     * @param $dirname
     */
    public function deleteDir($dirname)
    {
        $dir = $this->absolutizePath($dirname);
        Util::deleteDir($dir);
    }

    /**
     * Copies directory with all contents
     *
     * ``` php
     * <?php
     * $I->copyDir('vendor','old_vendor');
     * ?>
     * ```
     *
     * @param $src
     * @param $dst
     */
    public function copyDir($src, $dst)
    {
        Util::copyDir($src, $dst);
    }

    /**
     * Checks If opened file has `text` in it.
     *
     * Usage:
     *
     * ``` php
     * <?php
     * $I->openFile('composer.json');
     * $I->seeInThisFile('codeception/codeception');
     * ?>
     * ```
     *
     * @param $text
     */
    public function seeInThisFile($text)
    {
        $this->assertContains($text, $this->file, "No text '$text' in currently opened file");
    }

    /**
     * Checks If opened file has the `number` of new lines.
     *
     * Usage:
     *
     * ``` php
     * <?php
     * $I->openFile('composer.json');
     * $I->seeNumberNewLines(5);
     * ?>
     * ```
     *
     * @param int $number New lines
     */
    public function seeNumberNewLines($number)
    {
        $lines = preg_split('/\n|\r/', $this->file);

        $this->assertTrue(
            (int) $number === count($lines),
            "The number of new lines does not match with $number"
        );
    }
    /**
     * Checks that contents of currently opened file matches $regex
     *
     * @param $regex
     */
    public function seeThisFileMatches($regex)
    {
        $this->assertRegExp($regex, $this->file, "Contents of currently opened file does not match '$regex'");
    }

    /**
     * Checks the strict matching of file contents.
     * Unlike `seeInThisFile` will fail if file has something more than expected lines.
     * Better to use with HEREDOC strings.
     * Matching is done after removing "\r" chars from file content.
     *
     * ``` php
     * <?php
     * $I->openFile('process.pid');
     * $I->seeFileContentsEqual('3192');
     * ?>
     * ```
     *
     * @param $text
     */
    public function seeFileContentsEqual($text)
    {
        $file = str_replace("\r", '', $this->file);
        \PHPUnit_Framework_Assert::assertEquals($text, $file);
    }

    /**
     * Checks If opened file doesn't contain `text` in it
     *
     * ``` php
     * <?php
     * $I->openFile('composer.json');
     * $I->dontSeeInThisFile('codeception/codeception');
     * ?>
     * ```
     *
     * @param $text
     */
    public function dontSeeInThisFile($text)
    {
        $this->assertNotContains($text, $this->file, "Found text '$text' in currently opened file");
    }

    /**
     * Deletes a file
     */
    public function deleteThisFile()
    {
        $this->deleteFile($this->filepath);
    }

    /**
     * Checks if file exists in path.
     * Opens a file when it's exists
     *
     * ``` php
     * <?php
     * $I->seeFileFound('UserModel.php','app/models');
     * ?>
     * ```
     *
     * @param $filename
     * @param string $path
     */
    public function seeFileFound($filename, $path = '')
    {
        if (file_exists($filename) and !$path) {
            $this->openFile($filename);
            $this->filepath = $filename;
            $this->debug($filename);
            \PHPUnit_Framework_Assert::assertFileExists($path . $filename);
            return;
        }

        $path = $this->absolutizePath($path);
        $this->debug($path);
        if (!file_exists($path)) {
            $this->fail("Directory does not exist: $path");
        }

        $files = Finder::create()->files()->name($filename)->in($path);
        foreach ($files as $file) {
            $file = $file->getRealPath();
            $this->openFile($file);
            $this->filepath = $file;
            $this->debug($file);
            \PHPUnit_Framework_Assert::assertFileExists($file);
            return;
        }
        $this->fail("$filename in $path");
    }

    /**
     * Checks if file does not exist in path
     *
     * @param $filename
     * @param string $path
     */
    public function dontSeeFileFound($filename, $path = '')
    {
        \PHPUnit_Framework_Assert::assertFileNotExists($path . $filename);
    }


    /**
     * Erases directory contents
     *
     * ``` php
     * <?php
     * $I->cleanDir('logs');
     * ?>
     * ```
     *
     * @param $dirname
     */
    public function cleanDir($dirname)
    {
        $path = $this->absolutizePath($dirname);
        Util::doEmptyDir($path);
    }

    /**
     * Saves contents to file
     *
     * @param $filename
     * @param $contents
     */
    public function writeToFile($filename, $contents)
    {
        file_put_contents($filename, $contents);
    }
}
