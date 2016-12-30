<?php

use Codeception\Module\Filesystem;
use Codeception\Util\Stub;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Codeception\Module\Filesystem
     */
    protected $module;

    public function setUp()
    {
        $this->module = new Filesystem(make_container());
        $this->module->_before(Stub::makeEmpty('\Codeception\Test\Test'));
    }


    public function tearDown()
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

    /**
     * @expectedException PHPUnit_Framework_AssertionFailedError
     * @expectedExceptionMessage File "does-not-exist" not found at
     */
    public function testSeeFileFoundFailsWhenFileDoesNotExist()
    {
        $this->module->seeFileFound('does-not-exist');
    }

    /**
     * @expectedException PHPUnit_Framework_AssertionFailedError
     * @expectedExceptionMessageRegExp  /Directory does not exist: .*does-not-exist/
     */
    public function testSeeFileFoundFailsWhenPathDoesNotExist()
    {
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

    /**
     * @expectedException PHPUnit_Framework_AssertionFailedError
     * @expectedExceptionMessage Failed asserting that file "tests/data/dumps/mysql.sql" does not exist.
     */
    public function testDontSeeFileFoundFailsWhenFileExists()
    {
        $this->module->dontSeeFileFound('tests/data/dumps/mysql.sql');
    }

    /**
     * @expectedException PHPUnit_Framework_AssertionFailedError
     * @expectedExceptionMessageRegExp  /Directory does not exist: .*does-not-exist/
     */
    public function testDontSeeFileFoundFailsWhenPathDoesNotExist()
    {
        $this->module->dontSeeFileFound('mysql.sql', 'does-not-exist');
    }

    /**
     * @expectedException PHPUnit_Framework_AssertionFailedError
     * @expectedExceptionMessageRegExp /Failed asserting that file ".*tests\/data\/dumps\/mysql.sql" does not exist/
     */
    public function testDontSeeFileFoundFailsWhenFileExistsInSubdirectoryOfPath()
    {
        $this->module->dontSeeFileFound('mysql.sql', 'tests/data/');
    }
}
