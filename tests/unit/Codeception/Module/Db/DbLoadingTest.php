<?php

abstract class DbLoadingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\Module\Db
     */
    protected $module;

    abstract public function getConfig();
    abstract public function getPopulator();

    protected function setUp()
    {
        $this->module = new \Codeception\Module\Db(
            make_container(),
            array_replace($this->getConfig(), ['populate' => false, 'cleanup' => false])
        );
        $this->module->_beforeSuite();
        $this->module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        $this->assertFalse($this->module->populated);
        $this->module->driver->cleanup();
    }

    protected function tearDown()
    {
        $this->module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
    }

    public function testCleanupDatabase()
    {

        try {
            $this->module->dontSeeInDatabase('users', ['name' => 'davert']);
        } catch(\PDOException $noTable) {
            $noTable;
            // No table was found...
        }

        $this->module->_reconfigure([
            'populate' => true,
            'populator' => $this->getPopulator(),
            'cleanup' => true,
        ]);
        $this->module->_loadDump();
        $this->module->seeInDatabase('users', ['name' => 'davert']);
    }
}
