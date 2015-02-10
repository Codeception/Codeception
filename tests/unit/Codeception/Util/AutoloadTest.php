<?php

require_once 'MockAutoload.php';

use Codeception\Util\MockAutoload as Autoload;

class AutoloadTest extends PHPUnit_Framework_TestCase {

    protected function setUp()
    {
        Autoload::setFiles([
            '/vendor/foo.bar/src/ClassName.php',
            '/vendor/foo.bar/src/DoomClassName.php',
            '/vendor/foo.bar/tests/ClassNameTest.php',
            '/vendor/foo.bardoom/src/ClassName.php',
            '/vendor/foo.bar.baz.dib/src/ClassName.php',
            '/vendor/foo.bar.baz.dib.zim.gir/src/ClassName.php',
            '/vendor/src/ClassName.php',
            '/vendor/src/Foo/Bar/AnotherClassName.php',
            '/vendor/src/Bar/Baz/ClassName.php',
        ]);

        Autoload::addNamespace('Foo\Bar', '/vendor/foo.bar/src');
        Autoload::addNamespace('Foo\Bar', '/vendor/foo.bar/tests');
        Autoload::addNamespace('Foo\BarDoom', '/vendor/foo.bardoom/src');
        Autoload::addNamespace('Foo\Bar\Baz\Dib', '/vendor/foo.bar.baz.dib/src');
        Autoload::addNamespace('Foo\Bar\Baz\Dib\Zim\Gir', '/vendor/foo.bar.baz.dib.zim.gir/src');
        Autoload::addNamespace('', '/vendor/src');
    }

    public function testExistingFile()
    {
        $actual = Autoload::load('Foo\Bar\ClassName');
        $expect = '/vendor/foo.bar/src/ClassName.php';
        $this->assertSame($expect, $actual);

        $actual = Autoload::load('Foo\Bar\ClassNameTest');
        $expect = '/vendor/foo.bar/tests/ClassNameTest.php';
        $this->assertSame($expect, $actual);
    }

    public function testMissingFile()
    {
        $actual = Autoload::load('No_Vendor\No_Package\NoClass');
        $this->assertFalse($actual);
    }

    public function testDeepFile()
    {
        $actual = Autoload::load('Foo\Bar\Baz\Dib\Zim\Gir\ClassName');
        $expect = '/vendor/foo.bar.baz.dib.zim.gir/src/ClassName.php';
        $this->assertSame($expect, $actual);
    }

    public function testConfusion()
    {
        $actual = Autoload::load('Foo\Bar\DoomClassName');
        $expect = '/vendor/foo.bar/src/DoomClassName.php';
        $this->assertSame($expect, $actual);

        $actual = Autoload::load('Foo\BarDoom\ClassName');
        $expect = '/vendor/foo.bardoom/src/ClassName.php';
        $this->assertSame($expect, $actual);
    }

    public function testEmptyPrefix()
    {
        $actual = Autoload::load('ClassName');
        $expect = '/vendor/src/ClassName.php';
        $this->assertSame($expect, $actual);

        $actual = Autoload::load('Foo\Bar\AnotherClassName');
        $expect = '/vendor/src/Foo/Bar/AnotherClassName.php';
        $this->assertSame($expect, $actual);

        $actual = Autoload::load('Bar\Baz\ClassName');
        $expect = '/vendor/src/Bar/Baz/ClassName.php';
        $this->assertSame($expect, $actual);
    }
}
