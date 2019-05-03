<?php

use Codeception\Util\Stub;

class FTPTest extends \Codeception\PHPUnit\TestCase
{
    protected $config = array(
        'host' => '127.0.0.1',
        'tmp' => 'temp',
        'user' => 'user',
        'password' => 'password'
    );

    /**
     * @var \Codeception\Module\FTP
     */
    protected $module = null;

    public function _setUp()
    {
        $this->module = new \Codeception\Module\FTP(make_container());
        $this->module->_setConfig($this->config);

        $this->module->_before(Stub::makeEmpty('\Codeception\Test\Test'));
    }

    /**
     * Disabled Test - for travis testing, requires testing server
     */
    public function flow()
    {
        // Check root directory
        $this->assertEquals('/', $this->module->grabDirectory());

        // Create directory
        $this->module->makeDir('TESTING');
        // Move to new directory
        $this->module->amInPath('TESTING');
        // Verify current directory
        $this->assertEquals('/TESTING', $this->module->grabDirectory());

        $files = $this->module->grabFileList();
        // Create files on server
        $this->module->writeToFile('test_ftp_123.txt', 'some data added here');
        $this->module->writeToFile('test_ftp_567.txt', 'some data added here');
        $this->module->writeToFile('test_ftp_678.txt', 'some data added here');

        // Grab file list
        $files = $this->module->grabFileList();
        // Verify files are listed
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

        // Open file (download local copy)
        $this->module->openFile('test_ftp_123.txt');
        $this->module->seeInThisFile('data');

        $this->module->dontSeeInThisFile('banana');
        $this->module->seeFileContentsEqual('some data added here');

        $this->module->renameFile('test_ftp_678.txt', 'test_ftp_987.txt');

        $files = $this->module->grabFileList();
        // Verify old file is not listed
        $this->assertNotContains('test_ftp_678.txt', $files);
        // Verify renamed file is listed
        $this->assertContains('test_ftp_987.txt', $files);

        $this->module->deleteFile('test_ftp_123.txt');

        $files = $this->module->grabFileList();
        // Verify deleted file is not listed
        $this->assertNotContains('test_ftp_123.txt', $files);

        // Move to root directory
        $this->module->amInPath('/');

        $this->assertEquals('/', $this->module->grabDirectory());

        $this->module->renameDir('TESTING', 'TESTING_NEW');

        // Remove directory (with contents)
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

    public function _tearDown()
    {
        $this->module->_after(Stub::makeEmpty('\Codeception\Test\Test'));
    }
}
