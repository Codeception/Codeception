<?php
namespace Codeception\Module;

/**
 * Works with SQL dabatase.
 *
 * The most important function of this module is cleaning database before each test.
 * That's why this module was added into global configuration file: codeception.yml.
 * To have your database properly cleaned you should configure it to access the database.
 *
 * In order to have your database populated with data you need a raw SQL dump.
 * Just put it in ``` tests/_data ``` dir (by default) and specify path to it in config.
 * Next time after database is cleared all your data will be restored from dump.
 * Don't forget to include CREATE TABLE statements into it.
 *
 * Performance may dramatically change when using SQLite file database storage.
 * Consider converting your database into SQLite3 format with one of [provided tools](http://www.sqlite.org/cvstrac/wiki?p=ConverterTools).
 * While using SQLite database not recreated from SQL dump, but a database file is copied itself. So database repopulation is just about copying file.
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
 * ## Config
 *
 * * dsn *required* - PDO DSN
 * * user *required* - user to access database
 * * password *required* - password
 * * dump - path to database dump.
 * * populate: true - should the dump be loaded before test suite is started.
 * * cleanup: true - should the dump be reloaded after each test
 *
 * Also provides actions to perform checks in database.
 *
 * ## Public Properties
 * * dbh - contains PDO connection.
 *
 */

use \Codeception\Util\Driver\Db as Driver;

class Db extends \Codeception\Module implements \Codeception\Util\DbInterface
{

    /**
     * @api
     * @var
     */
    public $dbh;

    protected $sql = array();

    protected $config = array('populate' => true,
                              'cleanup'  => true,
                              'dump'     => null);

    /**
     * @var \Codeception\Util\Driver\Db
     */
    protected $driver;

    protected $requiredFields = array('dsn', 'user', 'password');

    public function _initialize()
    {
        if ($this->config['dump'] && ($this->config['cleanup'] or ($this->config['populate']))) {

            if (!file_exists(getcwd() . DIRECTORY_SEPARATOR . $this->config['dump'])) {
                throw new \Codeception\Exception\ModuleConfig(__CLASS__, "
                    File with dump deesn't exist.\n
                    Please, check path for sql file: " . $this->config['dump']);
            }
            $sql = file_get_contents(getcwd() . DIRECTORY_SEPARATOR . $this->config['dump']);
            $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s', "", $sql);
            $this->sql = explode("\n", $sql);
        }

        try {
            $this->driver = Driver::create($this->config['dsn'], $this->config['user'], $this->config['password']);
        } catch (\PDOException $e) {
            throw new \Codeception\Exception\Module(__CLASS__, $e->getMessage() . ' while creating PDO connection');
        }

        // starting with loading dump
        if ($this->config['populate']) {
                $this->cleanup();
                $this->loadDump();
        }
    }

    public function _after(\Codeception\TestCase $test)
    {
        if ($this->config['cleanup']) {
                $this->cleanup();
                $this->loadDump();
        }
    }

    protected function cleanup()
    {
        $dbh = $this->driver->getDbh();
        if (!$dbh) {
            throw new \Codeception\Exception\ModuleConfig(__CLASS__, "No connection to database. Remove this module from config if you don't need database repopulation");
        }
        try {
            // don't clear database for empty dump
            if (!count($this->sql)) return;
            $this->driver->cleanup();

        } catch (\Exception $e) {
            throw new \Codeception\Exception\Module(__CLASS__, $e->getMessage());
        }
    }

    protected function loadDump()
    {
        if (!$this->sql) return;
        try {
            $this->driver->load($this->sql);
        } catch (\PDOException $e) {
            throw new \Codeception\Exception\Module(__CLASS__, $e->getMessage());
        }
    }

    public function seeInDatabase($table, $criteria = array())
    {
        $res = $this->proceedSeeInDatabase($table, $criteria);
        \PHPUnit_Framework_Assert::assertGreaterThan(0, $res);
    }

    public function dontSeeInDatabase($table, $criteria = array())
    {
        $res = $this->proceedSeeInDatabase($table, $criteria);
        \PHPUnit_Framework_Assert::assertLessThan(1, $res);
    }

    protected function proceedSeeInDatabase($table, $criteria)
    {
        $query = "select count(*) from `%s` where %s";

        $params = array();
        foreach ($criteria as $k => $v) {
            $params[] = "`$k` = ? ";
        }
        $params = implode('AND ', $params);

        $query = sprintf($query, $table, $params);

        $this->debugSection('Query', $query, $params);

        $sth = $this->driver->getDbh()->prepare($query);
        if (!$sth) \PHPUnit_Framework_Assert::fail("Query '$query' can't be executed.");

        $sth->execute(array_values($criteria));
        return $sth->fetchColumn();
    }

}
