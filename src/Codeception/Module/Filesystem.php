<?php
namespace Codeception\Module;

class Filesystem extends \Codeception\Module
{


    protected $file = null;
    protected $filepath = null;

    protected $path = '';

    public function _before(\Codeception\TestCase $test)
    {
        $this->path = \Codeception\Configuration::projectDir();
    }

    public function amInPath($path)
    {
        chdir($this->path = $this->absolutizePath($path));
        $this->debug('Moved to ' . getcwd());
    }

    protected function absolutizePath($path)
    {
        // *nix way
        if (strpos($path, '/') === 0) return $path;
        // windows
        if (strpos($path, ':\\') === 1) return $path;

        return $this->path . DIRECTORY_SEPARATOR . $path;
    }

    public function openFile($filename)
    {
        $this->file = file_get_contents($this->absolutizePath($filename));
    }

    public function deleteFile($filename)
    {
        if (!file_exists($this->absolutizePath($filename))) \PHPUnit_Framework_Assert::fail('file not found');
        unlink($this->absolutizePath($filename));
    }

    public function deleteDir($dirname)
    {
        $dir = $this->absolutizePath($dirname);
        if (!file_exists($dir)) return true;
        if (!is_dir($dir) || is_link($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!$this->deleteDir($dir . "/" . $item)) {
                chmod($dir . "/" . $item, 0777);
                if (!$this->deleteDir($dir . "/" . $item)) return false;
            }
        }
        return rmdir($dir);
    }

    public function copyDir($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->copyDir($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function seeInThisFile($text)
    {
        \PHPUnit_Framework_Assert::assertContains($text, $this->file, "text $text in currently opened file");
    }

    public function dontSeeInThisFile($text)
    {
        \PHPUnit_Framework_Assert::assertNotContains($text, $this->file, "text $text in currently opened file");
    }

    public function deleteThisFile()
    {
        $this->deleteFile($this->filepath);
    }

    public function seeFileFound($filename, $path = '')
    {
        $path = $this->absolutizePath($path);

        $this->debug($path);

        if (!file_exists($path)) \PHPUnit_Framework_Assert::fail("Directiry does not exist: $path");


        $files = \Symfony\Component\Finder\Finder::create()->files()->name($filename)->in($path);
        foreach ($files as $file) {
            $file = $file->getRealPath();
            $this->openFile($file);
            $this->filepath = $file;
            $this->debug($file);
            \PHPUnit_Framework_Assert::assertFileExists($file);
            return;
        }
        \PHPUnit_Framework_Assert::fail("$filename in $path");
    }
}