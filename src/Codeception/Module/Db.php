<?php
namespace Codeception\Module;

/**
 * Works with SQL dabatase.
 *
 * The most important function of this module is cleaning database before each test.
 * That's why this module was added into global configuration file: codeception.yml.
 * To have your database properly cleaned you should configure it to access the database.
 * Also provides actions to perform checks in database.
 *
 * In order to have your database populated with data you need a raw SQL dump.
 * Just put it in ``` tests/_data ``` dir (by default) and specify path to it in config.
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
 * * Orcale
 *
 * Connection is done by database Drivers, which are stored in Codeception\Util\Driver namespace.
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
 * Example
 * 
 *   modules: 
 *      enabled: [Db]
 *      config:
 *         Db:
 *            dsn: 'mysql:host=localhost;dbname=testdb'
 *            username: 'root'
 *            password: ''
 *            dump: 'tests/_data/dump.sql'
 *            populate: true
 *            cleanup: false
 *
 * ## Public Properties
 * * dbh - contains PDO connection.
 * * driver - contains Connection Driver. See [list all available drivers](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Util/Driver)
 *
 */

use Codeception\Util\Driver\Db as Driver;
use Codeception\Exception\Module as ModuleException;
use Codeception\Exception\ModuleConfig as ModuleConfigException;

class Db extends \Codeception\Module implements \Codeception\Util\DbInterface
{

    /**
     * @api
     * @var
     */
    public $dbh;

    /**
     * @var
     */

    protected $sql = array();

    protected $config = array('populate' => true,
                              'cleanup'  => true,
                              'dump'     => null);

	protected $populated = false;

    /**
     * @var \Codeception\Util\Driver\Db
     */
    public $driver;

    protected $requiredFields = array('dsn', 'user', 'password');

    public function _initialize()
    {
        if ($this->config['dump'] && ($this->config['cleanup'] or ($this->config['populate']))) {

            if (!file_exists(getcwd() . DIRECTORY_SEPARATOR . $this->config['dump'])) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "\nFile with dump deesn't exist.
                    Please, check path for sql file: " . $this->config['dump']
                );
            }
            $sql = file_get_contents(getcwd() . DIRECTORY_SEPARATOR . $this->config['dump']);
            $sql = preg_replace('%/\*(?!!\d+)(?:(?!\*/).)*\*/%s', "", $sql);
            $this->sql = explode("\n", $sql);
        }

        try {
            $this->driver = Driver::create($this->config['dsn'], $this->config['user'], $this->config['password']);
        } catch (\PDOException $e) {
            throw new ModuleException(__CLASS__, $e->getMessage() . ' while creating PDO connection');
        }

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
        parent::_after($test);
    }

    protected function cleanup()
    {
        $dbh = $this->driver->getDbh();
        if (! $dbh) {
            throw new ModuleConfigException(
                __CLASS__,
                "No connection to database. Remove this module from config if you don't need database repopulation"
            );
        }
        try {
            // don't clear database for empty dump
            if (! count($this->sql)) {
                return;
            }
            $this->driver->cleanup();
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    protected function loadDump()
    {
        if (! $this->sql) {
            return;
        }
        try {
            $this->driver->load($this->sql);
        } catch (\PDOException $e) {
            throw new ModuleException(
                __CLASS__,
                $e->getMessage() . "\nSQL query being executed: " . $this->sql
            );
        }
    }

    public function seeInDatabase($table, $criteria = array())
    {
        $res = $this->proceedSeeInDatabase($table, 'count(*)', $criteria);
        \PHPUnit_Framework_Assert::assertGreaterThan(0, $res);
    }

    public function dontSeeInDatabase($table, $criteria = array())
    {
        $res = $this->proceedSeeInDatabase($table, 'count(*)',$criteria);
        \PHPUnit_Framework_Assert::assertLessThan(1, $res);
    }

    protected function proceedSeeInDatabase($table, $column, $criteria)
    {
        $query = $this->driver->select($column, $table, $criteria);
        $this->debugSection('Query', $query, json_encode($criteria));

        $sth = $this->driver->getDbh()->prepare($query);
        if (!$sth) \PHPUnit_Framework_Assert::fail("Query '$query' can't be executed.");

        $sth->execute(array_values($criteria));
        return $sth->fetchColumn();
    }

    public function grabFromDatabase($table, $column, $criteria = array()) {
        return $this->proceedSeeInDatabase($table, $column, $criteria);
    }

}
