<?php
namespace Codeception\Module;

/**
 * Works with SQL dabatase (MySQL tested).
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

class Db extends \Codeception\Module
{

    /**
     * @api
     * @var
     */
    public $dbh;

    protected $sql = array();

    protected $config = array('populate' => true, 'cleanup' => true, 'dump' => null, 'snapshot' => true);

    protected $requiredFields = array('dsn', 'user', 'password');

    protected $firstRun = true;

    public function _initialize()
    {
        if (!file_exists($this->config['dump'])) {
            throw new \Codeception\Exception\ModuleConfig(__CLASS__, "
                File with dump deesn't exist.\n
                Please, check path for sql file: " . $this->config['dump']);
        }

        // not necessary to specify dump
        if (isset($this->config['dump']) && ($this->config['cleanup'] or ($this->config['populate']))) {
            $sql = file_get_contents($this->config['dump']);
            $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s',"",$sql);
            $this->sql = explode("\r\n", $sql);
        }

        try {
            $dbh = new \PDO($this->config['dsn'], $this->config['user'], $this->config['password']);
            $this->dbh = $dbh;
        } catch (\PDOException $e) {
            throw new \Codeception\Exception\Module(__CLASS__, $e->getMessage().' while creating PDO connection');
        }

        // starting with loading dump
        if ($this->config['cleanup'] or $this->config['populate']) {
            $this->cleanup();
            $this->loadDump();
            if ($this->config['cleanup'] && $this->config['snapshot']) $this->createSnapshot();
        }
    }

    public function _before(\Codeception\TestCase $test)
    {
        if ($this->config['cleanup'] && !$this->firstRun) {
            $this->cleanup();
            $this->config['snapshot'] ? $this->restoreSnapshot() : $this->loadDump();
        }
        $this->firstRun = false;
    }

    protected function cleanup()
    {
        $dbh = $this->dbh;
        if (!$dbh) {
            throw new \Codeception\Exception\ModuleConfig(__CLASS__, "No connection to database. Remove this module from config if you don't need database repopulation");
        }
        try {
            // don't clear database for empty dump
            if (!count($this->sql)) return;

            $this->dbh->exec('SET FOREIGN_KEY_CHECKS=0');

            $res = $this->dbh->query('show tables')->fetchAll();
            foreach ($res as $row) {
                $table = $row[0];
                if (strpos($table,'__codeception_snapshot_') === 0) continue;
                $dbh->exec('drop table ' . $row[0]);
            }

            $this->dbh->exec('SET FOREIGN_KEY_CHECKS=1');

        } catch (\Exception $e) {
            throw new \Codeception\Exception\Module(__CLASS__, $e->getMessage());
        }
    }

    protected function loadDump()
    {
        if (!$this->sql) return;
        $dbh = $this->dbh;

        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=0');

        $query = "";
        foreach ($this->sql as $sql_line) {
            if (trim($sql_line) != "" && trim($sql_line) != ";") {
                $query .= $sql_line;
                if (substr(rtrim($query), -1,1) == ';') {
                    $this->dbh->exec($query);
                    $query = "";
                }
            }
        }
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=1');

    }

    protected function createSnapshot()
    {
        if (!$this->sql) return;
        $dbh = $this->dbh;

        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=0');

        $res = $this->dbh->query('show tables')->fetchAll();
        foreach ($res as $row) {
            $table = $row[0];
            if (strpos($table,'__codeception_snapshot_')===0) continue;
            $dbh->exec("CREATE TABLE __codeception_snapshot_$table AS SELECT * FROM $table;");
        }
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function restoreSnapshot()
    {
        if (!$this->sql) return;
        $dbh = $this->dbh;

        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=0');

        $res = $this->dbh->query('show tables')->fetchAll();
        foreach ($res as $row) {
            $table = $row[0];
            if (strpos($table,'__codeception_snapshot_') !==0 ) continue;
            $table = substr($table,23);
            $dbh->exec("CREATE TABLE $table AS SELECT * FROM __codeception_snapshot_$table;");
        }
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Checks if a row with given column values exists.
     * Provide table name and column values.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->seeInDatabase('users', array('name' => 'Davert', 'email' => 'davert@mail.com'));
     *
     * ```
     * Will generate:
     *
     * ``` sql
     * SELECT COUNT(*) FROM `users` WHERE `name` = 'Davert' AND `email` = 'davert@mail.com'
     * ```
     * Fails if no such user found.
     *
     * @param $table
     * @param array $criteria
     */
    public function seeInDatabase($table, $criteria = array())
    {
        $res = $this->proceedSeeInDatabase($table, $criteria);
        \PHPUnit_Framework_Assert::assertGreaterThan(0, $res);

    }

    /**
     * Effect is opposite to ->seeInDatabase
     *
     * Checks if there is no record with such column values in database.
     * Provide table name and column values.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->seeInDatabase('users', array('name' => 'Davert', 'email' => 'davert@mail.com'));
     *
     * ```
     * Will generate:
     *
     * ``` sql
     * SELECT COUNT(*) FROM `users` WHERE `name` = 'Davert' AND `email` = 'davert@mail.com'
     * ```
     * Fails if such user was found.
     *
     * @param $table
     * @param array $criteria
     */
    public function dontSeeInDatabase($table, $criteria)
    {
        $res = $this->proceedSeeInDatabase($table, $criteria);
        \PHPUnit_Framework_Assert::assertLessThan(1, $res);
    }

    protected function proceedSeeInDatabase($table, $criteria)
    {
        $query = "select count(*) from `%s` where %s";

        $params = array();
        foreach ($criteria as $k => $v) {
            $params[] = "`$k` = ?";
        }
        $params = implode('AND ',$params);

        $query = sprintf($query, $table, $params);

        $this->debugSection('Query',$query, $params);

        $sth = $this->dbh->prepare($query);
        $sth->execute(array_values($criteria));
        return $sth->fetchColumn();
    }

}
