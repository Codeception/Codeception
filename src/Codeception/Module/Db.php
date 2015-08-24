<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\Configuration;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Interfaces\Db as DbInterface;
use Codeception\Lib\Driver\Db as Driver;
use Codeception\TestCase;

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
 * * reconnect: false - should the module reconnect to database before each test
 *
 * ### Example
 *
 *     modules:
 *        enabled:
 *           - Db:
 *              dsn: 'mysql:host=localhost;dbname=testdb'
 *              user: 'root'
 *              password: ''
 *              dump: 'tests/_data/dump.sql'
 *              populate: true
 *              cleanup: false
 *              reconnect: true
 *
 * ### SQL data dump
 * 
 *  * Comments are permitted.
 *  * The `dump.sql` may contain multiline statements.
 *   * The delimiter, a semi-colon in this case, must be on the same line as the last statement:
 *  
 * ```sql
 * -- Add a few contacts to the table.
 * REPLACE INTO `Contacts` (`created`, `modified`, `status`, `contact`, `first`, `last`) VALUES
 * (NOW(), NOW(), 1, 'Bob Ross', 'Bob', 'Ross'),
 * (NOW(), NOW(), 1, 'Fred Flintstone', 'Fred', 'Flintstone');
 * 
 * -- Remove existing orders for testing.
 * DELETE FROM `Order`;
 * ```
 * 
 * ## Public Properties
 * * dbh - contains PDO connection.
 * * driver - contains Connection Driver. See [list all available drivers](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Util/Driver)
 *
 */
class Db extends CodeceptionModule implements DbInterface
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
        'cleanup' => true,
        'reconnect' => false,
        'dump' => null
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
    protected $insertedRows = [];

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
                    "\nFile with dump doesn't exist.\n"
                    . "Please, check path for sql file: "
                    . $this->config['dump']
                );
            }
            $sql = file_get_contents(Configuration::projectDir() . $this->config['dump']);
            $sql = preg_replace('%/\*(?!!\d+)(?:(?!\*/).)*\*/%s', "", $sql);
            if (!empty($sql)) {
                $this->sql = explode("\n", $sql);
            }
        }

        $this->connect();

        // starting with loading dump
        if ($this->config['populate']) {
            if ($this->config['cleanup']) {
                $this->cleanup();
            }
            $this->loadDump();
            $this->populated = true;
        }
    }

    private function connect()
    {
        try {
            $this->driver = Driver::create($this->config['dsn'], $this->config['user'], $this->config['password']);
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            if ($message === 'could not find driver') {
                list ($missingDriver,) = explode(':', $this->config['dsn'], 2);
                $message = "could not find $missingDriver driver";
            }

            throw new ModuleException(__CLASS__, $message . ' while creating PDO connection');
        }

        $this->dbh = $this->driver->getDbh();
    }

    private function disconnect()
    {
        $this->dbh = null;
        $this->driver = null;
    }

    public function _before(TestCase $test)
    {
        if ($this->config['reconnect']) {
            $this->connect();
        }
        if ($this->config['cleanup'] && !$this->populated) {
            $this->cleanup();
            $this->loadDump();
        }
        parent::_before($test);
    }

    public function _after(TestCase $test)
    {
        $this->populated = false;
        $this->removeInserted();
        if ($this->config['reconnect']) {
            $this->disconnect();
        }
        parent::_after($test);
    }

    protected function removeInserted()
    {
        foreach (array_reverse($this->insertedRows) as $row) {
            try {
                $this->driver->deleteQueryByCriteria($row['table'], $row['primary']);
            } catch (\Exception $e) {
                $this->debug("coudn't delete record " . json_encode($row['primary']) ." from {$row['table']}");
            }
        }
        $this->insertedRows = [];
    }

    protected function cleanup()
    {
        $dbh = $this->driver->getDbh();
        if (!$dbh) {
            throw new ModuleConfigException(
                __CLASS__,
                'No connection to database. Remove this module from config if you don\'t need database repopulation'
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

        $this->driver->executeQuery($query, array_values($data));

        try {
            $lastInsertId = (int)$this->driver->lastInsertId($table);
        } catch (\PDOException $e) {
            // ignore errors due to uncommon DB structure,
            // such as tables without _id_seq in PGSQL
            $lastInsertId = 0;
        }

        $this->addInsertedRow($table, $data, $lastInsertId);

        return $lastInsertId;
    }

    private function addInsertedRow($table, array $row, $id)
    {
        $primaryKey = $this->driver->getPrimaryKey($table);
        $primary = [];
        if ($primaryKey) {
            if ($id && count($primaryKey) === 1) {
                $primary [$primaryKey[0]] = $id;
            } else {
                foreach ($primaryKey as $column) {
                    if (isset($row[$column])) {
                        $primary[$column] = $row[$column];
                    } else {
                        throw new \InvalidArgumentException('Primary key field ' . $column . ' is not set for table ' . $table);
                    }
                }
            }
        } else {
            $primary = $row;
        }

        $this->insertedRows[] = [
            'table' => $table,
            'primary' => $primary,
        ];
    }

    public function seeInDatabase($table, $criteria = [])
    {
        $res = $this->countInDatabase($table, $criteria);
        $this->assertGreaterThan(0, $res, 'No matching records found for criteria ' . json_encode($criteria) . ' in table ' . $table);
    }

    /**
     * Asserts that found number of records in database
     *
     * ``` php
     * <?php
     * $I->seeNumRecords(1, 'users', ['name' => 'davert'])
     * ?>
     * ```
     *
     * @param int    $expectedNumber      Expected number
     * @param string $table    Table name
     * @param array  $criteria Search criteria [Optional]
     */
    public function seeNumRecords($expectedNumber, $table, array $criteria = [])
    {
        $actualNumber = $this->countInDatabase($table, $criteria);
        $this->assertEquals($expectedNumber, $actualNumber, 'The number of found rows (' . $actualNumber. ') does not match expected number ' . $expectedNumber . ' for criteria ' . json_encode($criteria) . ' in table ' . $table);
    }

    public function dontSeeInDatabase($table, $criteria = [])
    {
        $count = $this->countInDatabase($table, $criteria);
        $this->assertLessThan(1, $count, 'Unexpectedly found matching records for criteria ' . json_encode($criteria) . ' in table ' . $table);
    }

    /**
     * Count rows in database
     *
     * @param string $table    Table name
     * @param array  $criteria Search criteria [Optional]
     *
     * @return int
     */
    protected function countInDatabase($table, array $criteria = [])
    {
        return (int) $this->proceedSeeInDatabase($table, 'count(*)', $criteria);
    }

    protected function proceedSeeInDatabase($table, $column, $criteria)
    {
        $query = $this->driver->select($column, $table, $criteria);
        $this->debugSection('Query', $query, json_encode($criteria));

        $sth = $this->driver->executeQuery($query, array_values($criteria));

        return $sth->fetchColumn();
    }

    public function grabFromDatabase($table, $column, $criteria = [])
    {
        return $this->proceedSeeInDatabase($table, $column, $criteria);
    }
}
