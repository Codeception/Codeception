<?php

use Codeception\Util\Stub;

/**
 * Module for testing remote ftp systems.
 *
 * ## Status
 *
 * Maintainer: **nathanmac**
 * Stability: **stable**
 * Contact: nathan.macnamara@outlook.com
 *
 */
class SFTPTest extends \PHPUnit_Framework_TestCase
{
    protected $config = array(
        'host' => '127.0.0.1',
        'port' => 22,
        'tmp' => 'temp',
        'user' => 'user',
        'password' => 'password',
        'type' => 'sftp'
    );

    /**
     * @var \Codeception\Module\FTP
     */
    protected $module = null;

    public function setUp()
    {
        $this->module = new \Codeception\Module\FTP(make_container());
        $this->module->_setConfig($this->config);

        $this->module->_before(Stub::make('\Codeception\TestCase'));
    }

    /**
     * Disabled Test - for travis testing, requires testing server
     */
    public function flow()
    {
        $this->assertEquals('/', $this->module->grabDirectory());

        $this->module->makeDir('TESTING');
        $this->module->amInPath('TESTING');
        $this->assertEquals('/TESTING', $this->module->grabDirectory());

        $files = $this->module->grabFileList();
        $this->module->writeToFile('test_ftp_123.txt', 'some data added here');
        $this->module->writeToFile('test_ftp_567.txt', 'some data added here');
        $this->module->writeToFile('test_ftp_678.txt', 'some data added here');

        $files = $this->module->grabFileList();
        $this->assertContains('test_ftp_123.txt', $files);
        $this->assertContains('test_ftp_567.txt', $files);
        $this->assertContains('test_ftp_678.txt', $files);

        $this->module->seeFileFound('test_ftp_123.txt');
        $this->module->dontSeeFileFound('test_ftp_321.txt');
        $this->module->seeFileFoundMatches('/^test_ftp_([0-9]{3}).txt$/');
        $this->module->dontSeeFileFoundMatches('/^test_([0-9]{3})_ftp.txt$/');

        $this->assertGreaterThan(0, $this->module->grabFileCount());
        $this->assertGreaterThan(0, $this->module->grabFileSize('test_ftp_678.txt'));
        $this->assertGreaterThan(0, $this->module->grabFileModified('test_ftp_678.txt'));

        $this->module->openFile('test_ftp_567.txt');
        $this->module->deleteThisFile();
        $this->module->dontSeeFileFound('test_ftp_567.txt');

        $this->module->openFile('test_ftp_123.txt');
        $this->module->seeInThisFile('data');

        $this->module->dontSeeInThisFile('banana');
        $this->module->seeFileContentsEqual('some data added here');

        $this->module->renameFile('test_ftp_678.txt', 'test_ftp_987.txt');

        $files = $this->module->grabFileList();
        $this->assertNotContains('test_ftp_678.txt', $files);
        $this->assertContains('test_ftp_987.txt', $files);

        $this->module->deleteFile('test_ftp_123.txt');

        $files = $this->module->grabFileList();
        $this->assertNotContains('test_ftp_123.txt', $files);

        $this->module->amInPath('/');

        $this->assertEquals('/', $this->module->grabDirectory());

        $this->module->renameDir('TESTING', 'TESTING_NEW');

        $this->module->deleteDir('TESTING_NEW');

        // Test Clearing the Directory
        $this->module->makeDir('TESTING');
        $this->module->amInPath('TESTING');
        $this->module->writeToFile('test_ftp_123.txt', 'some data added here');
        $this->module->amInPath('/');
        $this->assertGreaterThan(0, $this->module->grabFileCount('TESTING'));
        $this->module->cleanDir('TESTING');
        $this->assertEquals(0, $this->module->grabFileCount('TESTING'));
        $this->module->deleteDir('TESTING');
    }

    public function tearDown()
    {
        $this->module->_after();
    }
}
