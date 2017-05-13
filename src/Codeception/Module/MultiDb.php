<?php

namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Driver\Db as Driver;
use Codeception\Module as CodeceptionModule;
use Codeception\TestInterface;
use Codeception\Lib\Interfaces\Db as DbInterface;

/**
 * Works with multiple SQL databases.
 *
 * based on original Db Module
 *
 * ## Config
 *
 * * connectionName:
 * * * dsn *required* - PDO DSN
 * * * user *required* - user to access database
 * * * password *required* - password
 * * * dump - path to database dump
 * * * populate: true - whether the the dump should be loaded before the test suite is started
 * * * cleanup: true - whether the dump should be reloaded before each test
 * *  *reconnect: false - whether the module should reconnect to the database before each test
 *
 * ## Example
 *
 *     modules:
 *        enabled:
 *           - MultiDb:
 *              primary:
 *                  dsn: 'mysql:host=localhost;dbname=testdb'
 *                  user: 'root'
 *                  password: ''
 *                  dump: 'tests/_data/dump_for_primary.sql'
 *                  populate: true
 *                  cleanup: false
 *                  reconnect: true
 *
 * ## SQL data dump
 *
 *  * Comments are permitted.
 *  * The `dump.sql` may contain multiline statements.
 *  * The delimiter, a semi-colon in this case, must be on the same line as the last statement:
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
 * ## Query generation
 *
 * seeInDatabase, dontSeeInDatabase, seeNumRecords, grabFromDatabase and grabNumRecords methods
 * accept arrays as criteria. WHERE condition is generated using item key as a field name and
 * item value as a field value.
 *
 * Example:
 * ```php
 * <?php
 * $I->seeInDatabase('users', array('name' => 'Davert', 'email' => 'davert@mail.com'));
 *
 * ```
 * Will generate:
 *
 * ```sql
 * SELECT COUNT(*) FROM `users` WHERE `name` = 'Davert' AND `email` = 'davert@mail.com'
 * ```
 * New addition to 2.1.9 is ability to use LIKE in condition. It is achieved by adding ' like' to column name.
 *
 * Example:
 * ```php
 * <?php
 * $I->seeInDatabase('users', array('name' => 'Davert', 'email like' => 'davert%'));
 *
 * ```
 * Will generate:
 *
 * ```sql
 * SELECT COUNT(*) FROM `users` WHERE `name` = 'Davert' AND `email` LIKE 'davert%'
 * ```
 * ## Public Properties
 * * dbh - contains the PDO connection
 * * driver - contains the Connection Driver
 *
 */

class MultiDb extends CodeceptionModule implements DbInterface
{
    /**
     * @var \PDO[]
     */
    public $connections = [];

    /**
     * @var \PDO
     */
    protected $currentConnection;

    /**
     * @var Driver
     */
    protected $currentDriver;

    /**
     * @var Driver[]
     */
    public $drivers = [];

    protected $config = [
        'connections' => null
    ];

    /**
     * @var array
     */
    protected $sql = [];

    /**
     * @var array
     */
    protected $insertedRows = [];

    /**
     * @var array
     */
    protected $requiredFields = ['connections'];

    /**
     * @var array
     */
    protected $connectionRequiredFields = ['dsn', 'user', 'password'];

    /**
     * @var array
     */
    protected $populated = [];

    public function _initialize()
    {
        $validConfig = false;

        if (is_array($this->config['connections'])) {
            foreach ($this->config['connections'] as $db => $connectionConfig) {
                $params = array_keys($connectionConfig);

                if (isset($connectionConfig['populate'])) {
                    $this->populated[$db] = $connectionConfig['populate'];
                } else {
                    $this->populated[$db] = true;
                }

                if (array_intersect($this->connectionRequiredFields, $params) == $this->connectionRequiredFields) {
                    $validConfig = true;
                }

                if (!$validConfig) {
                    throw new ModuleConfigException(
                        __CLASS__,
                        "\nOptions: " . implode(', ', $this->connectionRequiredFields) . " are required\n
                            Please, update the configuration and set all the required fields\n\n"
                    );
                }

                $this->insertedRows[$db] = [];
            }
        }

        foreach ($this->config['connections'] as $db => $connectionConfig) {
            $this->connect($db);

            if (isset($connectionConfig['dump'])
                && ((isset($connectionConfig['populate']) && $connectionConfig['populate'])
                    || (isset($connectionConfig['cleanup']) && $connectionConfig['cleanup']))) {
                $this->readSql($db);
            }

            if (isset($connectionConfig['populate']) && $connectionConfig['populate']) {
                if (isset($connectionConfig['cleanup']) && $connectionConfig['cleanup']) {
                    $this->cleanup($db);
                }

                $this->loadDump($db);
            }

            if (isset($connectionConfig['reconnect']) && $connectionConfig['reconnect']) {
                $this->disconnect($db);
            }
        }
    }

    private function readSql($connection)
    {
        $config = $this->config['connections'][$connection];

        if (!file_exists(Configuration::projectDir() . $config['dump'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "\nFile with dump doesn't exist.\n"
                . "Please, check path for sql file: "
                . $config['dump']
            );
        }

        $sql = file_get_contents(Configuration::projectDir() . $config['dump']);

        // remove C-style comments (except MySQL directives)
        $sql = preg_replace('%/\*(?!!\d+).*?\*/%s', '', $sql);

        if (!empty($sql)) {
            // split SQL dump into lines
            $this->sql[$connection] = preg_split('/\r\n|\n|\r/', $sql, -1, PREG_SPLIT_NO_EMPTY);
        }
    }

    protected function cleanup($connection)
    {
        $dbh = $this->drivers[$connection]->getDbh();
        if (!$dbh) {
            throw new ModuleConfigException(
                __CLASS__,
                'No connection to database. Remove this module from config if you don\'t need database repopulation'
            );
        }

        try {
            // don't clear database for empty dump
            if (!count($this->sql[$connection])) {
                return;
            }
            $this->drivers[$connection]->cleanup();
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    protected function loadDump($connection)
    {
        if (!array_key_exists($connection, $this->sql)) {
            return null;
        }

        try {
            $this->drivers[$connection]->load($this->sql[$connection]);
        } catch (\PDOException $e) {
            throw new ModuleException(
                __CLASS__,
                $e->getMessage() . "\nSQL query being executed: " . $this->drivers[$connection]->sqlToRun
            );
        }
    }

    private function connect($connection)
    {
        $config = $this->config['connections'][$connection];

        try {
            $this->drivers[$connection] = Driver::create($config['dsn'], $config['user'], $config['password']);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();

            if ($msg == 'could not find driver') {
                list ($missingDriver, ) = explode(':', $config['dsn'], 2);
                $msg = "could not find {$missingDriver} driver";
            }

            throw new ModuleException(__CLASS__, $msg . ' while creating PDO connection');
        }

        $this->connections[$connection] = $this->drivers[$connection]->getDbh();
    }

    private function disconnect($connection)
    {
        unset($this->connections[$connection]);
        unset($this->drivers[$connection]);
    }

    public function _before(TestInterface $test)
    {
        foreach ($this->config['connections'] as $db => $connectionConfig) {
            if ($connectionConfig['reconnect']) {
                $this->connect($db);
            }

            if ($connectionConfig['cleanup'] && !$this->populated[$db]) {
                $this->cleanup($db);
                $this->loadDump($db);
            }
        }

        parent::_before($test);
    }

    public function _after(TestInterface $test)
    {
        foreach ($this->config['connections'] as $db => $connectionConfig) {
            $this->populated[$db] = false;
            $this->removeInserted($db);

            if ($connectionConfig['reconnect']) {
                $this->disconnect($db);
            }
        }

        parent::_after($test);
    }

    protected function removeInserted($connection)
    {
        foreach (array_reverse($this->insertedRows[$connection]) as $row) {
            try {
                $this->drivers[$connection]->deleteQueryByCriteria($row['table'], $row['primary']);
            } catch (\Exception $e) {
                $this->debug("couldn't delete record " . json_encode($row['primary']) ." from {$row['table']}");
            }

            $this->insertedRows[$connection] = [];
        }
    }

    /**
     * Select connection
     * This method must be first before other data tests
     *
     * ```php
     * <?php
     * $I->amConnectedToDatabase('primary');
     * ?>
     * ```
     *
     * @param $database string
     */
    public function amConnectedToDatabase($database)
    {
        $this->currentConnection = $database;
        $this->currentDriver = $this->drivers[$database];
    }

    /**
     * Inserts an SQL record into a database. This record will be erased after the test.
     *
     * ```php
     * <?php
     * $I->haveInDatabase('users', array('name' => 'miles', 'email' => 'miles@davis.com'));
     * ?>
     * ```
     *
     * @param string $table
     * @param array $data
     *
     * @return integer $id
     */
    public function haveInDatabase($table, array $data)
    {
        $query = $this->currentDriver->insert($table, $data);
        $params = array_values($data);
        $this->debugSection('Query', $query);
        $this->debugSection('Parameters', $params);
        $this->currentDriver->executeQuery($query, $params);

        try {
            $lastInsertId = (int)$this->currentDriver->lastInsertId($table);
        } catch (\PDOException $e) {
            $lastInsertId = 0;
        }

        $this->addInsertedRow($this->currentConnection, $table, $data, $lastInsertId);

        return $lastInsertId;
    }

    private function addInsertedRow($connection, $table, array $row, $id)
    {
        $primaryKey = $this->currentDriver->getPrimaryKey($table);
        $primary = [];
        if ($primaryKey) {
            if ($id && count($primaryKey) === 1) {
                $primary[$primaryKey[0]] = $id;
            } else {
                foreach ($primaryKey as $column) {
                    if (isset($row[$column])) {
                        $primary[$column] = $row[$column];
                    } else {
                        throw new \InvalidArgumentException("Primary key field {$column} is not set for table {$table}");
                    }
                }
            }
        } else {
            $primary = $row;
        }

        $this->insertedRows[$connection][] = [
            'table' => $table,
            'primary' => $primary
        ];
    }

    /**
     * Select data from database by criteria
     *
     * ```php
     * <?php
     * $I->seeInDatabase('users', array('name' => 'Davert', 'email like' => 'davert%'));
     * ?>
     * ```
     *
     * @param string $table
     * @param array $criteria
     */
    public function seeInDatabase($table, $criteria = [])
    {
        $result = $this->countInDatabase($table, $criteria);
        $this->assertGreaterThan(
            0,
            $result,
            'No matching records found for criteria ' . json_encode($criteria) . ' in table ' . $table
        );
    }

    /**
     * Asserts that the given number of records were found in the database.
     *
     * ```php
     * <?php
     * $I->seeNumRecords(1, 'users', ['name' => 'davert'])
     * ?>
     * ```
     *
     * @param int $expectedNumber Expected number
     * @param string $table Table name
     * @param array $criteria Search criteria [Optional]
     */
    public function seeNumRecords($expectedNumber, $table, array $criteria = [])
    {
        $actualNumber = $this->countInDatabase($table, $criteria);
        $this->assertEquals(
            $expectedNumber,
            $actualNumber,
            sprintf(
                'The number of found rows (%d) does not match expected number %d for criteria %s in table %s',
                $actualNumber,
                $expectedNumber,
                json_encode($criteria),
                $table
            )
        );
    }

    /**
     * Asserts that the record was not found in database.
     *
     * ```php
     * <?php
     * $I->dontSeeInDatabase('users', ['name' => 'davert'])
     * ?>
     * ```
     *
     * @param string $table Table name
     * @param array $criteria Search criteria
     */
    public function dontSeeInDatabase($table, $criteria = [])
    {
        $count = $this->countInDatabase($table, $criteria);
        $this->assertLessThan(
            1,
            $count,
            'Unexpectedly found matching records for criteria ' . json_encode($criteria) . ' in table ' . $table
        );
    }

    protected function countInDatabase($table, array $criteria = [])
    {
        return (int)$this->proceedSeeInDatabase($table, 'count(*)', $criteria);
    }

    protected function proceedSeeInDatabase($table, $column, array $criteria = [])
    {
        $query = $this->currentDriver->select($column, $table, $criteria);
        $params = array_values($criteria);
        $this->debugSection('Query', $query);
        if (!empty($params)) {
            $this->debugSection('Parameters', $params);
        }
        $sth = $this->currentDriver->executeQuery($query, $params);

        return $sth->fetchColumn();
    }

    public function grabFromDatabase($table, $column, $criteria = [])
    {
        return $this->proceedSeeInDatabase($table, $column, $criteria);
    }

    /**
     * Returns the number of rows in a database
     *
     * @param string $table    Table name
     * @param array  $criteria Search criteria [Optional]
     *
     * @return int
     */
    public function grabNumRecords($table, array $criteria = [])
    {
        return $this->countInDatabase($table, $criteria);
    }

    /**
     * Checking connection in config
     *
     * @param string $connection
     */
    public function checkDatabaseInConfig($connection)
    {
        $this->assertArrayHasKey(
            $connection,
            $this->drivers,
            "No match found {$connection} on " . json_encode($this->drivers)
        );
    }

    /**
     * Run plain sql queries as PDO Statement
     *
     * @param string $query
     * @param array $params
     */
    public function amRunPlainSql($query, array $params = [])
    {
        $this->debugSection('Query', $query);
        $this->debugSection('Parameters', $params);

        $this->currentDriver->executeQuery($query, $params);
    }
}
