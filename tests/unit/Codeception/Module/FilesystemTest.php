<?php

use Codeception\Module\Filesystem;
use Codeception\Util\Stub;
use PHPUnit\Framework\AssertionFailedError;

class FilesystemTest extends \Codeception\PHPUnit\TestCase
{

    /**
     * @var \Codeception\Module\Filesystem
     */
    protected $module;

    public function _setUp()
    {
        $this->module = new Filesystem(make_container());
        $this->module->_before(Stub::makeEmpty('\Codeception\Test\Test'));
    }


    public function _tearDown()
    {
        $this->module->_after(Stub::makeEmpty('\Codeception\Test\Test'));
    }

    public function testSeeFileFoundPassesWhenFileExists()
    {
        $this->module->seeFileFound('tests/data/dumps/mysql.sql');
    }

    public function testSeeFileFoundPassesWhenFileExistsInSubdirectoryOfPath()
    {
        $this->module->seeFileFound('mysql.sql', 'tests/data/');
    }

    public function testSeeFileFoundFailsWhenFileDoesNotExist()
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('File "does-not-exist" not found at');
        $this->module->seeFileFound('does-not-exist');
    }

    public function testSeeFileFoundFailsWhenPathDoesNotExist()
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp('/Directory does not exist: .*does-not-exist/');
        $this->module->seeFileFound('mysql.sql', 'does-not-exist');
    }

    public function testDontSeeFileFoundPassesWhenFileDoesNotExists()
    {
        $this->module->dontSeeFileFound('does-not-exist');
    }

    public function testDontSeeFileFoundPassesWhenFileDoesNotExistsInPath()
    {
        $this->module->dontSeeFileFound('does-not-exist', 'tests/data/');
    }

    public function testDontSeeFileFoundFailsWhenFileExists()
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that file "tests/data/dumps/mysql.sql" does not exist.');
        $this->module->dontSeeFileFound('tests/data/dumps/mysql.sql');
    }

    public function testDontSeeFileFoundFailsWhenPathDoesNotExist()
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp('/Directory does not exist: .*does-not-exist/');
        $this->module->dontSeeFileFound('mysql.sql', 'does-not-exist');
    }

    public function testDontSeeFileFoundFailsWhenFileExistsInSubdirectoryOfPath()
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp('/Failed asserting that file ".*tests\/data\/dumps\/mysql.sql" does not exist/');
        $this->module->dontSeeFileFound('mysql.sql', 'tests/data/');
    }
}
