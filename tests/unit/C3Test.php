<?php

use Codeception\Configuration;

class C3Test extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    public $c3 = null;

    /**
     * @var string
     */
    public $c3_dir = null;

    protected function setUp()
    {
        if (!extension_loaded('xdebug')) {
            $this->markTestSkipped('xdebug extension required for c3test.');
        }

        $this->c3 = Configuration::dataDir() . 'claypit/c3.php';
        $this->c3_dir = Codeception\Configuration::outputDir() . 'c3tmp/';
        @mkdir($this->c3_dir, 0777, true);

        $_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE'] = 'test';
        $_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_DEBUG'] = 'debug';
    }

    protected function tearDown()
    {
        unset($_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_DEBUG']);
        unset($_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE']);
        \Codeception\Util\FileSystem::deleteDir($this->c3_dir);
    }

    public function testC3CodeCoverageStarted()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('This test fails on HHVM');
        }
        $_SERVER['REQUEST_URI'] = '/';
        include $this->c3;
        $this->assertInstanceOf('PHP_CodeCoverage', $codeCoverage);
    }

    public function testCodeCoverageRestrictedAccess()
    {
        unset($_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE']);
        include $this->c3;
        $this->assertFalse(isset($config_file));
        $this->assertFalse(isset($requested_c3_report));
    }

    public function testCodeCoverageCleanup()
    {
        $_SERVER['REQUEST_URI'] = '/c3/report/clear';
        $cc_file = $this->c3_dir . 'dummy.txt';
        file_put_contents($cc_file, 'nothing');
        include $this->c3;
        $this->assertEquals('clear', $route);
        $this->assertFileNotExists($cc_file);
    }

    public function testCodeCoverageHtmlReport()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Remote coverage HTML report does not work on HHVM');
        }
        $_SERVER['REQUEST_URI'] = '/c3/report/html';
        include $this->c3;
        $this->assertEquals('html', $route);
        $this->assertFileExists($this->c3_dir . 'codecoverage.tar');
    }

    public function testCodeCoverageXmlReport()
    {
        $_SERVER['REQUEST_URI'] = '/c3/report/clover';
        include $this->c3;
        $this->assertEquals('clover', $route);
        $this->assertFileExists($this->c3_dir . 'codecoverage.clover.xml');
    }

    public function testCodeCoverageSerializedReport()
    {
        $_SERVER['REQUEST_URI'] = '/c3/report/serialized';
        include $this->c3;
        $this->assertEquals('serialized', $route);
        $this->assertInstanceOf('PHP_CodeCoverage', $codeCoverage);
    }
}
