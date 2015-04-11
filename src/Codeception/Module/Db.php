<?php
namespace Codeception\Module;

/**
 * Works with SQL database.
 *
 * The most important function of this module is cleaning database before each test.
 * That's why this module was added into global configuration file: codeception.yml.
 * To have your database properly cleaned you should configure it to access the database.
 * Also provides actions to perform checks in database.
 *
 * In order to have your database populated with data you need a raw SQL dump.
 * Just put it in `tests/_data` dir (by default) and specify path to it in config.
 * Next time after database is cleared all your data will be restored from dump.
 * Don't forget to include CREATE TABLE statements into it.
 *
 * Supported and tested databases are:
 *
 * * MySQL
 * * SQLite (only file)
 * * PostgreSQL
 *
 * Supported but not tested.
 *
 * * MSSQL
 * * Oracle
 *
 * Connection is done by database Drivers, which are stored in Codeception\Lib\Driver namespace.
 * Check out drivers if you get problems loading dumps and cleaning databases.
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * stability:
 *     - Mysql: **stable**
 *     - SQLite: **stable**
 *     - Postgres: **beta**
 *     - MSSQL: **alpha**
 *     - Oracle: **alpha**
 * * Contact: codecept@davert.mail.ua
 *
 * *Please review the code of non-stable modules and provide patches if you have issues.*
 *
 * ## Config
 *
 * * dsn *required* - PDO DSN
 * * user *required* - user to access database
 * * password *required* - password
 * * dump - path to database dump.
 * * populate: true - should the dump be loaded before test suite is started.
 * * cleanup: true - should the dump be reloaded after each test
 *
 * ### Example
 *
 *     modules:
 *        enabled: [Db]
 *        config:
 *           Db:
 *              dsn: 'mysql:host=localhost;dbname=testdb'
 *              user: 'root'
 *              password: ''
 *              dump: 'tests/_data/dump.sql'
 *              populate: true
 *              cleanup: false
 *
 * ## Public Properties
 * * dbh - contains PDO connection.
 * * driver - contains Connection Driver. See [list all available drivers](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Util/Driver)
 *
 */

use Codeception\Configuration as Configuration;
use Codeception\Exception\ModuleException as ModuleException;
use Codeception\Exception\ModuleConfigException as ModuleConfigException;
use Codeception\Lib\Driver\Db as Driver;

class Db extends \Codeception\Module implements \Codeception\Lib\Interfaces\Db
{

    /**
     * @api
     * @var
     */
    public $dbh;

    /**
     * @var array
     */
    protected $sql = [];

    /**
     * @var array
     */
    protected $config = [
      'populate' => true,
      'cleanup'  => true,
      'dump'     => null
    ];

    /**
     * @var bool
     */
    protected $populated = false;

    /**
     * @var \Codeception\Lib\Driver\Db
     */
    public $driver;

    /**
     * @var array
     */
    protected $insertedIds = [];

    /**
     * @var array
     */
    protected $requiredFields = ['dsn', 'user', 'password'];

    public function _initialize()
    {
        if ($this->config['dump'] && ($this->config['cleanup'] or ($this->config['populate']))) {

            if (!file_exists(Configuration::projectDir() . $this->config['dump'])) {
                throw new ModuleConfigException(
                  __CLASS__,
                  "\nFile with dump doesn't exist.\n" .
                  "Please, check path for sql file: " . $this->config['dump']
                );
            }
            $sql = file_get_contents(Configuration::projectDir() . $this->config['dump']);
            $sql = preg_replace('%/\*(?!!\d+)(?:(?!\*/).)*\*/%s', "", $sql);
            if (!empty($sql)) {
                $this->sql = explode("\n", $sql);
            }
        }

        try {
            $this->driver = Driver::create($this->config['dsn'], $this->config['user'], $this->config['password']);
        } catch (\PDOException $e) {
            throw new ModuleException(__CLASS__, $e->getMessage() . ' while creating PDO connection');
        }

        $this->dbh = $this->driver->getDbh();

        // starting with loading dump
        if ($this->config['populate']) {
            $this->cleanup();
            $this->loadDump();
            $this->populated = true;
        }
    }

    public function _before(\Codeception\TestCase $test)
    {
        if ($this->config['cleanup'] && !$this->populated) {
            $this->cleanup();
            $this->loadDump();
        }
        parent::_before($test);
    }

    public function _after(\Codeception\TestCase $test)
    {
        $this->populated = false;
        $this->removeInserted();
        parent::_after($test);
    }

    protected function removeInserted()
    {
        foreach ($this->insertedIds as $insertId) {
            try {
                $this->driver->deleteQuery($insertId['table'], $insertId['id'], $insertId['primary']);
            } catch (\Exception $e) {
                $this->debug("coudn\'t delete record {$insertId['id']} from {$insertId['table']}");
            }
        }
        $this->insertedIds = [];
    }

    protected function cleanup()
    {
        $dbh = $this->driver->getDbh();
        if (!$dbh) {
            throw new ModuleConfigException(
              __CLASS__,
              "No connection to database. Remove this module from config if you don't need database repopulation"
            );
        }
        try {
            // don't clear database for empty dump
            if (!count($this->sql)) {
                return;
            }
            $this->driver->cleanup();
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    protected function loadDump()
    {
        if (!$this->sql) {
            return;
        }
        try {
            $this->driver->load($this->sql);
        } catch (\PDOException $e) {
            throw new ModuleException(
              __CLASS__,
              $e->getMessage() . "\nSQL query being executed: " . $this->driver->sqlToRun
            );
        }
    }

    /**
     * Inserts SQL record into database. This record will be erased after the test.
     *
     * ``` php
     * <?php
     * $I->haveInDatabase('users', array('name' => 'miles', 'email' => 'miles@davis.com'));
     * ?>
     * ```
     *
     * @param       $table
     * @param array $data
     *
     * @return integer $id
     */
    public function haveInDatabase($table, array $data)
    {
        $query = $this->driver->insert($table, $data);
        $this->debugSection('Query', $query);

        $sth = $this->driver->getDbh()->prepare($query);
        if (!$sth) {
            $this->fail("Query '$query' can't be executed.");
        }
        $i = 1;
        foreach ($data as $val) {
            $sth->bindValue($i, $val);
            $i++;
        }
        $res = $sth->execute();
        if (!$res) {
            $this->fail(sprintf("Record with %s couldn't be inserted into %s", json_encode($data), $table));
        }

        try {
            $lastInsertId = (int)$this->driver->lastInsertId($table);
        } catch (\PDOException $e) {
            // ignore errors due to uncommon DB structure,
            // such as tables without _id_seq in PGSQL
            $lastInsertId = 0;
        }
        $this->insertedIds[] = array('table' => $table, 'id' => $lastInsertId);

        $this->insertedIds[] = [
          'table'   => $table,
          'id'      => $lastInsertId,
          'primary' => $this->driver->getPrimaryColumn($table)
        ];

        return $lastInsertId;
    }

    public function seeInDatabase($table, $criteria = [])
    {
        $res = $this->proceedSeeInDatabase($table, 'count(*)', $criteria);
        \PHPUnit_Framework_Assert::assertGreaterThan(0, $res, 'No matching records found');
    }

    public function dontSeeInDatabase($table, $criteria = [])
    {
        $res = $this->proceedSeeInDatabase($table, 'count(*)', $criteria);
        \PHPUnit_Framework_Assert::assertLessThan(1, $res);
    }

    protected function proceedSeeInDatabase($table, $column, $criteria)
    {
        $query = $this->driver->select($column, $table, $criteria);
        $this->debugSection('Query', $query, json_encode($criteria));

        $sth = $this->driver->getDbh()->prepare($query);
        if (!$sth) {
            $this->fail("Query '$query' can't be executed.");
        }

        $sth->execute(array_values($criteria));

        return $sth->fetchColumn();
    }

    public function grabFromDatabase($table, $column, $criteria = [])
    {
        return $this->proceedSeeInDatabase($table, $column, $criteria);
    }
}
