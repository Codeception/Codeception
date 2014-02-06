<?php

use Codeception\Util\Stub;
use Codeception\Lib\Driver\Db as Driver;

class DbhTest extends \PHPUnit_Framework_TestCase
{
    protected $config = array(
        'dsn' => 'sqlite:tests/data/sqlite.db',
        'user' => 'root',
        'password' => ''
    );

    protected $testCase = null;

    /**
     * @var \Codeception\Module\Dbh
     */
    protected $module = null;

    public function setUp() {
      $this->testCase = Stub::make('\Codeception\TestCase');

      $module = new \Codeception\Module\Dbh();

      try {
        $driver = Driver::create($this->config['dsn'], $this->config['user'], $this->config['password']);
        $module::$dbh = $driver->getDbh();
      } catch (\PDOException $e) {
          $this->markTestSkipped('Coudn\'t establish connection to database');
          return;
      }

      $this->module = $module;
    }

    public function testDontRollbackOutsideATransaction() {
      $this->module->_after($this->testCase);
    }

}
