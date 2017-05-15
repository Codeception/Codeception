<?php

abstract class DbCleaningTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\Module\Db
     */
    protected $module;

    abstract public function getConfig();

    protected function setUp()
    {
        $this->module = new \Codeception\Module\Db(make_container(), $this->getConfig());
        $this->module->_beforeSuite();
        $this->module->_before(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
        $this->assertTrue($this->module->isPopulated());
    }

    protected function tearDown()
    {
        $this->module->_after(\Codeception\Util\Stub::makeEmpty('\Codeception\TestInterface'));
    }

    public function testCleanupDatabase()
    {
        $this->module->seeInDatabase('users', ['name' => 'davert']);
        $ref = new \ReflectionObject($this->module);
        $cleanupMethod = $ref->getMethod('cleanup');
        $cleanupMethod->setAccessible(true);
        $cleanupMethod->invoke($this->module);

        // Since table does not exist it should fail
        // TODO: Catch this exception at the driver level and re-throw a general one
        // just for "table not found" across all the drivers
        $this->expectException(\PDOException::class);

        $this->module->dontSeeInDatabase('users', ['name' => 'davert']);
    }

}
