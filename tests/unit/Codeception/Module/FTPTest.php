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
class FTPTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $this->module = new \Codeception\Module\FTP();
        $this->module->_setConfig($this->config);

        $this->module->_before(Stub::make('\Codeception\TestCase'));
    }

    /**
     * Disabled Test - for travis testing, requires testing server
     */
    public function flow()
    {
        $this->assertEquals('/', $this->module->grabDirectory());                           // Check root directory

        $this->module->makeDir('TESTING');                                                  // Create directory
        $this->module->amInPath('TESTING');                                                 // Move to new directory
        $this->assertEquals('/TESTING', $this->module->grabDirectory());                    // Verify currency directory

        $files = $this->module->grabFileList();
        $this->module->writeToFile('test_ftp_123.txt', 'some data added here');             // Create file on server
        $this->module->writeToFile('test_ftp_567.txt', 'some data added here');             // Create file on server
        $this->module->writeToFile('test_ftp_678.txt', 'some data added here');             // Create file on server

        $files = $this->module->grabFileList();                                             // Grab file list
        $this->assertContains('test_ftp_123.txt', $files);                                  // Verify file is listed
        $this->assertContains('test_ftp_567.txt', $files);                                  // Verify file is listed
        $this->assertContains('test_ftp_678.txt', $files);                                  // Verify file is listed

        $this->module->seeFileFound('test_ftp_123.txt');                                    // I seeFileFound test
        $this->module->dontSeeFileFound('test_ftp_321.txt');                                // I dontSeeFileFound test
        $this->module->seeFileFoundMatches('/^test_ftp_([0-9]{3}).txt$/');                  // I seeFileFoundMatches test
        $this->module->dontSeeFileFoundMatches('/^test_([0-9]{3})_ftp.txt$/');              // I dontSeeFileFoundMatches test

        $this->assertGreaterThan(0, $this->module->grabFileCount());                        // Grab the file count
        $this->assertGreaterThan(0, $this->module->grabFileSize('test_ftp_678.txt'));       // Grab the file size
        $this->assertGreaterThan(0, $this->module->grabFileModified('test_ftp_678.txt'));   // Grab the file modified time

        $this->module->openFile('test_ftp_567.txt');                                        // Open file (download local copy)
        $this->module->deleteThisFile();                                                    // Delete open file
        $this->module->dontSeeFileFound('test_ftp_567.txt');                                // I dontSeeFileFound test

        $this->module->openFile('test_ftp_123.txt');                                        // Open file (download local copy)
        $this->module->seeInThisFile('data');                                               // Look in file to see if contains 'data'

        $this->module->dontSeeInThisFile('banana');                                         // Look in file, don't see 'banana'
        $this->module->seeFileContentsEqual('some data added here');                        // Look in file to see if only contains 'some data added here'

        $this->module->renameFile('test_ftp_678.txt', 'test_ftp_987.txt');                  // Rename file

        $files = $this->module->grabFileList();                                             // Grab file list
        $this->assertNotContains('test_ftp_678.txt', $files);                               // Verify old file is not listed
        $this->assertContains('test_ftp_987.txt', $files);                                  // Verify renamed file is listed

        $this->module->deleteFile('test_ftp_123.txt');                                      // Delete file on server

        $files = $this->module->grabFileList();                                             // Grab file list
        $this->assertNotContains('test_ftp_123.txt', $files);                               // Verify old file is not listed

        $this->module->amInPath('/');                                                       // Move to root directory

        $this->assertEquals('/', $this->module->grabDirectory());                           // Check root directory

        $this->module->renameDir('TESTING', 'TESTING_NEW');                                 // Rename directory

        $this->module->deleteDir('TESTING_NEW');                                            // Remove directory (with contents)

        // Test Clearing the Directory
        $this->module->makeDir('TESTING');                                                  // Create directory
        $this->module->amInPath('TESTING');                                                 // Move to new directory
        $this->module->writeToFile('test_ftp_123.txt', 'some data added here');             // Create file on server
        $this->module->amInPath('/');                                                       // Move to root directory
        $this->assertGreaterThan(0, $this->module->grabFileCount('TESTING'));               // Verify directory has contents
        $this->module->cleanDir('TESTING');                                                 // Clear directory
        $this->assertEquals(0, $this->module->grabFileCount('TESTING'));                    // Verify directory has no contents after clearDir
        $this->module->deleteDir('TESTING');                                                // Remove directory (with no contents)
    }

    public function tearDown()
    {
        $this->module->_after();
    }
}