<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Process\Tests;

use Symfony\Component\Process\PhpExecutableFinder;

/**
 * @author Robert Schönthal <seroscho@googlemail.com>
 */
class PhpExecutableFinderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * tests find() with the env var PHP_PATH
     */
    public function testFindWithPHP_PATH()
    {
        if (defined('PHP_BINARY')) {
            $this->markTestSkipped('The PHP binary is easily available as of PHP 5.4');
        }

        $f = new PhpExecutableFinder();

        $current = $f->find();

        //not executable PHP_PATH
        putenv('PHP_PATH=/not/executable/php');
        $this->assertFalse($f->find(), '::find() returns false for not executable php');

        //executable PHP_PATH
        putenv('PHP_PATH='.$current);
        $this->assertEquals($f->find(), $current, '::find() returns the executable php');
    }

    /**
     * tests find() with default executable
     */
    public function testFindWithSuffix()
    {
        if (defined('PHP_BINARY')) {
            $this->markTestSkipped('The PHP binary is easily available as of PHP 5.4');
        }

        putenv('PHP_PATH=');
        putenv('PHP_PEAR_PHP_BIN=');
        $f = new PhpExecutableFinder();

        $current = $f->find();

        //TODO maybe php executable is custom or even windows
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->assertTrue(is_executable($current));
            $this->assertTrue((bool) preg_match('/'.addSlashes(DIRECTORY_SEPARATOR).'php\.(exe|bat|cmd|com)$/i', $current), '::find() returns the executable php with suffixes');
        }
    }
}
