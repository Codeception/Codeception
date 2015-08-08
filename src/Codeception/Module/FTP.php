<?php
namespace Codeception\Module;

use Codeception\Module\Filesystem;
use Codeception\TestCase;

/**
 *
 * Works with SFTP/FTP servers.
 *
 * In order to test the contents of a specific file stored on any remote FTP/SFTP system
 * this module downloads a temporary file to the local system. The temporary directory is
 * defined by default as ```tests/_data``` to specify a different directory set the tmp config
 * option to your chosen path.
 *
 * Don't forget to create the folder and ensure its writable.
 *
 * Supported and tested FTP types are:
 *
 * * FTP
 * * SFTP
 *
 * Connection uses php build in FTP client for FTP, connection to SFTP uses [phpseclib](http://phpseclib.sourceforge.net/) pulled in using composer.
 *
 * For SFTP, add [phpseclib](http://phpseclib.sourceforge.net/) to require list.
 * ```
 * "require": {
 *  "phpseclib/phpseclib": "0.3.6"
 * }
 * ```
 *
 * ## Status
 *
 * * Maintainer: **nathanmac**
 * * Stability:
 *     - FTP: **stable**
 *     - SFTP: **stable**
 * * Contact: nathan.macnamara@outlook.com
 *
 * ## Config
 *
 * * type: ftp - type of connection ftp/sftp (defaults to ftp).
 * * host *required* - hostname/ip address of the ftp server.
 * * port: 21 - port number for the ftp server
 * * timeout: 90 - timeout settings for connecting the ftp server.
 * * user: anonymous - user to access ftp server, defaults to anonymous authentication.
 * * password - password, defaults to empty for anonymous.
 * * key - path to RSA key for sftp.
 * * tmp - path to local directory for storing tmp files.
 * * passive: true - Turns on or off passive mode (FTP only)
 * * cleanup: true - remove tmp files from local directory on completion.
 *
 * ### Example
 * #### Example (FTP)
 *
 *     modules:
 *        enabled: [FTP]
 *        config:
 *           FTP:
 *              type: ftp
 *              host: '127.0.0.1'
 *              port: 21
 *              timeout: 120
 *              user: 'root'
 *              password: 'root'
 *              key: ~/.ssh/id_rsa
 *              tmp: 'tests/_data/ftp'
 *              passive: true
 *              cleanup: false
 *
 * #### Example (SFTP)
 *
 *     modules:
 *        enabled: [FTP]
 *        config:
 *           FTP:
 *              type: sftp
 *              host: '127.0.0.1'
 *              port: 22
 *              timeout: 120
 *              user: 'root'
 *              password: 'root'
 *              key: ''
 *              tmp: 'tests/_data/ftp'
 *              cleanup: false
 *
 *
 * This module extends the Filesystem module, file contents methods are inherited from this module.
 */

class FTP extends Filesystem
{
    /**
     * FTP/SFTP connection handler
     */
    protected $ftp = null;

    /**
     * Configuration options and default settings
     *
     * @var array
     */
    protected $config = [
        'type'     => 'ftp',
        'port'     => 21,
        'timeout'  => 90,
        'user'     => 'anonymous',
        'password' => '',
        'key'      => '',
        'tmp'      => 'tests/_data',
        'passive'  => false,
        'cleanup'  => true
    ];

    /**
     * Required configuration fields
     *
     * @var array
     */
    protected $requiredFields = ['host'];

    // ----------- SETUP METHODS BELOW HERE -------------------------//

    /**
     * Setup connection and login with config settings
     *
     * @param \Codeception\TestCase $test
     */
    public function _before(TestCase $test)
    {
        // Login using config settings
        $this->loginAs($this->config['user'], $this->config['password']);
    }

    /**
     * Close the FTP connection & Clear up
     */
    public function _after()
    {
        $this->_closeConnection();

        // Clean up temp files
        if ($this->config['cleanup']) {
            if (file_exists($this->config['tmp'] . '/ftp_data_file.tmp')) {
                unlink($this->config['tmp'] . '/ftp_data_file.tmp');
            }
        }
    }

    /**
     * Change the logged in user mid-way through your test, this closes the
     * current connection to the server and initialises and new connection.
     *
     * On initiation of this modules you are automatically logged into
     * the server using the specified config options or defaulted
     * to anonymous user if not provided.
     *
     * ``` php
     * <?php
     * $I->loginAs('user','password');
     * ?>
     * ```
     *
     * @param String $user
     * @param String $password
     */
    public function loginAs($user = 'anonymous', $password = '')
    {
        $this->_openConnection($user, $password); // Create new connection and login.
    }

    /**
     * Enters a directory on the ftp system - FTP root directory is used by default
     *
     * @param $path
     */
    public function amInPath($path)
    {
        $this->_changeDirectory($this->path = $this->absolutizePath($path) . ($path == '/' ? '' : DIRECTORY_SEPARATOR));
        $this->debug('Moved to ' . $this->path);
    }

    /**
     * Resolve path
     *
     * @param $path
     * @return string
     */
    protected function absolutizePath($path)
    {
        if (strpos($path, '/') === 0) {
            return $path;
        }
        return $this->path . $path;
    }

    // ----------- SEARCH METHODS BELOW HERE ------------------------//

    /**
     * Checks if file exists in path on the remote FTP/SFTP system.
     * DOES NOT OPEN the file when it's exists
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
        $files = $this->grabFileList($path);
        $this->debug("see file: {$filename}");
        $this->assertContains($filename, $files, "file {$filename} not found in {$path}");
    }

    /**
     * Checks if file exists in path on the remote FTP/SFTP system, using regular expression as filename.
     * DOES NOT OPEN the file when it's exists
     *
     *  ``` php
     * <?php
     * $I->seeFileFoundMatches('/^UserModel_([0-9]{6}).php$/','app/models');
     * ?>
     * ```
     *
     * @param $regex
     * @param string $path
     */
    public function seeFileFoundMatches($regex, $path = '')
    {
        foreach ($this->grabFileList($path) as $filename) {
            preg_match($regex, $filename, $matches);
            if (!empty($matches)) {
                $this->debug("file '{$filename}' matches '{$regex}'");
                return;
            }
        }
        $this->fail("no file matches found for '{$regex}'");
    }

    /**
     * Checks if file does not exist in path on the remote FTP/SFTP system
     *
     * @param $filename
     * @param string $path
     */
    public function dontSeeFileFound($filename, $path = '')
    {
        $files = $this->grabFileList($path);
        $this->debug("don't see file: {$filename}");
        $this->assertNotContains($filename, $files);
    }

    /**
     * Checks if file does not exist in path on the remote FTP/SFTP system, using regular expression as filename.
     * DOES NOT OPEN the file when it's exists
     *
     * @param $regex
     * @param string $path
     */
    public function dontSeeFileFoundMatches($regex, $path = '')
    {
        foreach ($this->grabFileList($path) as $filename) {
            preg_match($regex, $filename, $matches);
            if (!empty($matches)) {
                $this->fail("file matches found for {$regex}");
            }
        }
        $this->assertTrue(true);
        $this->debug("no files match '{$regex}'");
    }

    // ----------- UTILITY METHODS BELOW HERE -------------------------//

    /**
     * Opens a file (downloads from the remote FTP/SFTP system to a tmp directory for processing) and stores it's content.
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
        $this->_openFile($this->absolutizePath($filename));
    }

    /**
     * Saves contents to tmp file and uploads the FTP/SFTP system.
     * Overwrites current file on server if exists.
     *
     * ``` php
     * <?php
     * $I->writeToFile('composer.json', 'some data here');
     * ?>
     * ```
     *
     * @param $filename
     * @param $contents
     */
    public function writeToFile($filename, $contents)
    {
        $this->_writeToFile($this->absolutizePath($filename), $contents);
    }

    /**
     * Create a directory on the server
     *
     * ``` php
     * <?php
     * $I->makeDir('vendor');
     * ?>
     * ```
     *
     * @param $dirname
     */
    public function makeDir($dirname)
    {
        $this->makeDirectory($this->absolutizePath($dirname));
    }

    /**
     * Currently not supported in this module, overwrite inherited method
     *
     * @param $src
     * @param $dst
     */
    public function copyDir($src, $dst)
    {
        $this->fail('copyDir() currently unsupported by FTP module');
    }

    /**
     * Rename/Move file on the FTP/SFTP server
     *
     * ``` php
     * <?php
     * $I->renameFile('composer.lock', 'composer_old.lock');
     * ?>
     * ```
     *
     * @param $filename
     * @param $rename
     */
    public function renameFile($filename, $rename)
    {
        $this->renameDirectory($this->absolutizePath($filename), $this->absolutizePath($rename));
    }

    /**
     * Rename/Move directory on the FTP/SFTP server
     *
     * ``` php
     * <?php
     * $I->renameDir('vendor', 'vendor_old');
     * ?>
     * ```
     *
     * @param $dirname
     * @param $rename
     */
    public function renameDir($dirname, $rename)
    {
        $this->renameDirectory($this->absolutizePath($dirname), $this->absolutizePath($rename));
    }

    /**
     * Deletes a file on the remote FTP/SFTP system
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
        $this->delete($this->absolutizePath($filename));
    }

    /**
     * Deletes directory with all subdirectories on the remote FTP/SFTP server
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
        $this->delete($this->absolutizePath($dirname));
    }

    /**
     * Erases directory contents on the FTP/SFTP server
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
        $this->clearDirectory($this->absolutizePath($dirname));
    }

    // ----------- GRABBER METHODS BELOW HERE -----------------------//


    /**
     * Grabber method for returning file/folders listing in an array
     *
     * ```php
     * <?php
     * $files = $I->grabFileList();
     * $count = $I->grabFileList('TEST', false); // Include . .. .thumbs.db
     * ?>
     * ```
     *
     * @param string $path
     * @param bool $ignore - suppress '.', '..' and '.thumbs.db'
     * @return array
     */
    public function grabFileList($path = '', $ignore = true)
    {
        $absolutize_path = $this->absolutizePath($path) . ($path != '' && substr($path, -1) != '/' ? DIRECTORY_SEPARATOR : '');
        $files = $this->_listFiles($absolutize_path);

        $display_files = [];
        if (is_array($files) && !empty($files)) {
            $this->debug('File List:');
            foreach ($files as &$file) {
                if (strtolower($file) != '.' &&
                    strtolower($file) != '..' &&
                    strtolower($file) != 'thumbs.db'
                ) {    // Ignore '.', '..' and 'thumbs.db'

                    $file = str_replace($absolutize_path, '', $file); // Replace full path from file listings if returned in listing
                    $display_files[] = $file;
                    $this->debug('    - ' . $file);
                }
            }
            return $ignore ? $display_files : $files;
        }
        $this->debug("File List: <empty>");
        return [];
    }

    /**
     * Grabber method for returning file/folders count in directory
     *
     * ```php
     * <?php
     * $count = $I->grabFileCount();
     * $count = $I->grabFileCount('TEST', false); // Include . .. .thumbs.db
     * ?>
     * ```
     *
     * @param string $path
     * @param bool $ignore - suppress '.', '..' and '.thumbs.db'
     * @return int
     */
    public function grabFileCount($path = '', $ignore = true)
    {
        $count = count($this->grabFileList($path, $ignore));
        $this->debug("File Count: {$count}");
        return $count;
    }

    /**
     * Grabber method to return file size
     *
     * ```php
     * <?php
     * $size = $I->grabFileSize('test.txt');
     * ?>
     * ```
     *
     * @param $filename
     * @return bool
     */
    public function grabFileSize($filename)
    {
        $fileSize = $this->size($filename);
        $this->debug("{$filename} has a file size of {$fileSize}");
        return $fileSize;
    }

    /**
     * Grabber method to return last modified timestamp
     *
     * ```php
     * <?php
     * $time = $I->grabFileModified('test.txt');
     * ?>
     * ```
     *
     * @param $filename
     * @return bool
     */
    public function grabFileModified($filename)
    {
        $time = $this->modified($filename);
        $this->debug("{$filename} was last modified at {$time}");
        return $time;
    }

    /**
     * Grabber method to return current working directory
     *
     * ```php
     * <?php
     * $pwd = $I->grabDirectory();
     * ?>
     * ```
     *
     * @return string
     */
    public function grabDirectory()
    {
        $pwd = $this->_directory();
        $this->debug("PWD: {$pwd}");
        return $pwd;
    }

    // ----------- SERVER CONNECTION METHODS BELOW HERE -------------//

    /**
     * Open a new FTP/SFTP connection and authenticate user.
     *
     * @param string $user
     * @param string $password
     */
    private function _openConnection($user = 'anonymous', $password = '')
    {
        $this->_closeConnection();   // Close connection if already open
        if ($this->isSFTP()) {
            $this->sftpConnect($user, $password);
        } else {
            $this->ftpConnect($user, $password);
        }
        $pwd = $this->grabDirectory();
        $this->path = $pwd . ($pwd == '/' ? '' : DIRECTORY_SEPARATOR);
    }

    /**
     * Close open FTP/SFTP connection
     */
    private function _closeConnection()
    {
        if (!$this->ftp) {
            return;
        }
        if (!$this->isSFTP()) {
            ftp_close($this->ftp);
        }
    }

    /**
     * Get the file listing for FTP/SFTP connection
     *
     * @param String $path
     * @return array
     */
    private function _listFiles($path)
    {
        if ($this->isSFTP()) {
            $files = @$this->ftp->nlist($path);
        } else {
            $files = @ftp_nlist($this->ftp, $path);
        }
        if ($files === false) {
            $this->fail("couldn't list files");
        }
        return $files;
    }

    /**
     * Get the current directory for the FTP/SFTP connection
     *
     * @return string
     */
    private function _directory()
    {
        if ($this->isSFTP()) {
            // == DIRECTORY_SEPARATOR ? '' : $pwd;
            $pwd = @$this->ftp->pwd();
        } else {
            $pwd = @ftp_pwd($this->ftp);
        }
        if (!$pwd) {
            $this->fail("couldn't get current directory");
        }
    }

    /**
     * Change the working directory on the FTP/SFTP server
     *
     * @param $path
     */
    private function _changeDirectory($path)
    {
        if ($this->isSFTP()) {
            $changed = @$this->ftp->chdir($path);
        } else {
            $changed = @ftp_chdir($this->ftp, $path);
        }
        if (!$changed) {
            $this->fail("couldn't change directory {$path}");
        }
    }

    /**
     * Download remote file to local tmp directory and open contents.
     *
     * @param $filename
     */
    private function _openFile($filename)
    {
        // Check local tmp directory
        if (!is_dir($this->config['tmp']) || !is_writeable($this->config['tmp'])) {
            $this->fail('tmp directory not found or is not writable');
        }

        // Download file to local tmp directory
        $tmp_file = $this->config['tmp'] . "/ftp_data_file.tmp";

        if ($this->isSFTP()) {
            $downloaded = @$this->ftp->get($filename, $tmp_file);
        } else {
            $downloaded = @ftp_get($this->ftp, $tmp_file, $filename, FTP_BINARY);
        }
        if (!$downloaded) {
            $this->fail('failed to download file to tmp directory');
        }

        // Open file content to variable
        if ($this->file = file_get_contents($tmp_file)) {
            $this->filepath = $filename;
        } else {
            $this->fail('failed to open tmp file');
        }
    }

    /**
     * Write data to local tmp file and upload to server
     *
     * @param $filename
     * @param $contents
     */
    private function _writeToFile($filename, $contents)
    {
        // Check local tmp directory
        if (!is_dir($this->config['tmp']) || !is_writeable($this->config['tmp'])) {
            $this->fail('tmp directory not found or is not writable');
        }

        // Build temp file
        $tmp_file = $this->config['tmp'] . "/ftp_data_file.tmp";
        file_put_contents($tmp_file, $contents);

        // Update variables
        $this->filepath = $tmp_file;
        $this->file = $contents;

        // Upload the file to server
        if ($this->isSFTP()) {
            $uploaded = @$this->ftp->put($filename, $tmp_file, NET_SFTP_LOCAL_FILE);
        } else {
            $uploaded = ftp_put($this->ftp, $filename, $tmp_file, FTP_BINARY);
        }
        if (!$uploaded) {
            $this->fail('failed to upload file to server');
        }
    }

    /**
     * Make new directory on server
     *
     * @param $path
     */
    private function makeDirectory($path)
    {
        if ($this->isSFTP()) {
            $created = @$this->ftp->mkdir($path, true);
        } else {
            $created = @ftp_mkdir($this->ftp, $path);
        }
        if (!$created) {
            $this->fail("couldn't make directory {$path}");
        }
        $this->debug("Make directory: {$path}");
    }

    /**
     * Rename/Move directory/file on server
     *
     * @param $path
     * @param $rename
     */
    private function renameDirectory($path, $rename)
    {
        if ($this->isSFTP()) {
            $renamed = @$this->ftp->rename($path, $rename);
        } else {
            $renamed = @ftp_rename($this->ftp, $path, $rename);
        }
        if (!$renamed) {
            $this->fail("couldn't rename directory {$path} to {$rename}");
        }
        $this->debug("Renamed directory: {$path} to {$rename}");
    }

    /**
     * Delete file on server
     *
     * @param $filename
     */
    private function delete($filename, $isDir = flase)
    {
        if ($this->isSFTP()) {
            $deleted = @$this->ftp->delete($filename, $isDir);
        } else {
            $deleted = @$this->ftpDelete($filename);
        }
        if (!$deleted) {
            $this->fail("couldn't delete {$filename}");
        }
        $this->debug("Deleted: {$filename}");
    }


    /**
     * Function to recursively delete folder, used for PHP FTP build in client.
     *
     * @param $directory
     * @return bool
     */
    private function ftpDelete($directory)
    {
        // here we attempt to delete the file/directory
        if (!(@ftp_rmdir($this->ftp, $directory) || @ftp_delete($this->ftp, $directory))) {
            // if the attempt to delete fails, get the file listing
            $filelist = @ftp_nlist($this->ftp, $directory);

            // loop through the file list and recursively delete the FILE in the list
            foreach ($filelist as $file) {
                $this->ftpDelete($file);
            }

            // if the file list is empty, delete the DIRECTORY we passed
            $this->ftpDelete($directory);
        }
        return true;
    }

    /**
     * Clear directory on server of all content
     *
     * @param $path
     */
    private function clearDirectory($path)
    {
        $this->debug("Clear directory: {$path}");
        $this->delete($path);
        $this->makeDirectory($path);
    }

    /**
     * Return the size of a given file
     *
     * @param $filename
     * @return bool
     */
    private function size($filename)
    {
        if ($this->isSFTP()) {
            $size = (int)@$this->ftp->size($filename);
        } else {
            $size = @ftp_size($this->ftp, $filename);
        }
        if ($size > 0) {
            return $size;
        }
        $this->fail("couldn't get the file size for {$filename}");
    }

    /**
     * Return the last modified time of a given file
     *
     * @param $filename
     * @return bool
     */
    private function modified($filename)
    {
        if ($this->isSFTP()) {
            $info = @$this->ftp->lstat($filename);
            if ($info) {
                return $info['mtime'];
            }
        } else {
            if ($time = @ftp_mdtm($this->ftp, $filename)) {
                return $time;
            }
        }
        $this->fail("couldn't get the file size for {$filename}");
    }

    /**
     * @param $user
     * @param $password
     */
    protected function sftpConnect($user, $password)
    {
        $this->ftp = new \Net_SFTP($this->config['host'], $this->config['port'], $this->config['timeout']);
        if ($this->ftp === false) {
            $this->ftp = null;
            $this->fail('failed to connect to ftp server');
        }

        if (isset($this->config['key'])) {
            $keyFile = file_get_contents($this->config['key']);
            $password = new \Crypt_RSA();
            $password->loadKey($keyFile);
        }

        if (!$this->ftp->login($user, $password)) {
            $this->fail('failed to authenticate user');
        }
    }

    /**
     * @param $user
     * @param $password
     */
    protected function ftpConnect($user, $password)
    {
        $this->ftp = ftp_connect($this->config['host'], $this->config['port'], $this->config['timeout']);
        if ($this->ftp === false) {
            $this->ftp = null;
            $this->fail('failed to connect to ftp server');
        }

        // Login using given access details
        if (!@ftp_login($this->ftp, $user, $password)) {
            $this->fail('failed to authenticate user');
        }

        // Set passive mode option (ftp only option)
        if (isset($this->config['passive'])) {
            ftp_pasv($this->ftp, $this->config['passive']);
        }
    }

    protected function isSFTP()
    {
        return strtolower($this->config['type']) == 'sftp';
    }
}
