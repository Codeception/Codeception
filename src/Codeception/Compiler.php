<?php

namespace Codeception;

use Symfony\Component\Finder\Finder;

class Compiler
{
    /**
     * Compile new composer.phar
     * @param string $filename
     */
    public function compile($filename = 'codecept.phar')
    {
        if(file_exists($filename)) {
            unlink($filename);
        }

        $phar = new \Phar($filename, 0, 'codecept.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->notName('Compiler.php')
            ->in(__DIR__ . '/..')
        ;

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $finder = new Finder();
        $finder
            ->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('*.js')
            ->name('*.tpl.dist')
            ->name('*.html.dist')
            ->exclude('Tests')
            ->exclude('tests')
            ->exclude('benchmark')
            ->exclude('demo')
            ->in(__DIR__.'/../../vendor')
        ;

        foreach($finder as $file) {
            $this->addFile($phar, $file);
        }

        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../autoload.php'));

        $phar->stopBuffering();

        if(in_array('GZ', \Phar::getSupportedCompression())) {
            echo "Compressed\r\n";
            //do not use compressFiles as it has issue with temporary file when adding large amount of files
//            $phar->compressFiles(\Phar::GZ);

            $phar = $phar->compress(\Phar::GZ);
            $this->setMainExecutable($phar);
            $this->setStub($phar);

            unlink('codecept.phar');
            rename('codecept.phar.gz', 'codecept.phar');

        } else {
            $this->setMainExecutable($phar);
            $this->setStub($phar);
        }


        unset($phar);
    }

    /**
     * Add file to phar archive
     * @param $phar
     * @param $file
     */
    public function addFile($phar, $file)
    {
        $path = str_replace(dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR, '', $file->getRealPath());

//        var_dump($path);

        $content = file_get_contents($file);
        $content = $this->stripWhitespace($content);

        $phar->addFromString($path, $content);
    }

    public function setMainExecutable($phar)
    {
        $phar->addFromString('codecept', file_get_contents(__DIR__.'/../../package/bin'));
    }

    public function setStub($phar)
    {
        $contents = file_get_contents(__DIR__.'/../../package/stub.php');
        $contents = preg_replace('{^#!/usr/bin/env php\s*}', '', $contents);
        $phar->setStub($contents);
    }

    /**
     * Strips whitespace from source. Taken from composer
     * @param $source
     * @return string
     */
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }
}