<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\Configuration;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Interfaces\Db as DbInterface;
use Codeception\Lib\Driver\Db as Driver;
use Codeception\Lib\DbPopulator;
use Codeception\TestInterface;

/**
 * Works with SQL database.
 *
 * The most important function of this module is to clean a database before each test.
 * That's why this module was added to the global configuration file `codeception.yml`.
 * To have your database properly cleaned you should configure it to access the database.
 * This module also provides actions to perform checks in a database.
 *
 * In order to have your database populated with data you need a raw SQL dump.
 * Simply put the dump in the `tests/_data` directory (by default) and specify the path in the config.
 * The next time after the database is cleared, all your data will be restored from the dump.
 * Don't forget to include `CREATE TABLE` statements in the dump.
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
 * Connection is done by database Drivers, which are stored in the `Codeception\Lib\Driver` namespace.
 * [Check out the drivers](https://github.com/Codeception/Codeception/tree/2.1/src/Codeception/Lib/Driver)
 * if you run into problems loading dumps and cleaning databases.
 *
 * ## Status
 *
 * * Maintainer: **Gintautas Miselis**
 * * stability:
 *     - Mysql: **stable**
 *     - SQLite: **stable**
 *     - Postgres: **beta**
 *     - MSSQL: **alpha**
 *     - Oracle: **alpha**
 *
 * *Please review the code of non-stable modules and provide patches if you have issues.*
 *
 * ## Config
 *
 * * dsn *required* - PDO DSN
 * * user *required* - user to access database
 * * password *required* - password
 * * dump - path to database dump
 * * populate: true - whether the the dump should be loaded before the test suite is started
 * * cleanup: true - whether the dump should be reloaded before each test
 * * reconnect: false - whether the module should reconnect to the database before each test
 *
 * ## Example
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
 *              populator: 'mysql -u root -h $host -D $dbname < $dump'
 *
 * ## SQL data dump
 *
 *  You now have two ways of loading the dump into your database. You could use the legacy method,
 *  where codeception reads the dump file and passes it to the specific driver so it populates the
 *  database (this method is usually slow in particular if your dump is "big") or you could
 *  configure a populator command (e.g. using the mysql client binary) that, after parsing the PDO
 *  dsn string and the module configuration, gives you access to some placeholder variables for a
 *  very simple command completion template.
 *
 *  Usage of the `populator` is simple, just set the command that you want to use to load the dump
 *  into the database with the parsed variables from the config and/or dsn string. All the configuration
 *  options will also be available and take priority over the dsn.
 *  Most dsn have a `keyword=value` format, so you should expect to have a variable named as the
 *  keyword with the full value inside it. If you need further parsing you could write your own
 *  script to wrap the real populator command/tool.
 *
 *  PDO dsn elements for the supported drivers:
 *  * MySQL: [PDO_MYSQL DSN](https://secure.php.net/manual/en/ref.pdo-mysql.connection.php)
 *  * SQLite: [PDO_SQLITE DSN](https://secure.php.net/manual/en/ref.pdo-sqlite.connection.php)
 *  * PostgreSQL: [PDO_PGSQL DSN](https://secure.php.net/manual/en/ref.pdo-pgsql.connection.php)
 *  * MSSQL: [PDO_SQLSRV DSN](https://secure.php.net/manual/en/ref.pdo-sqlsrv.connection.php)
 *  * Oracle: [PDO_OCI DSN](https://secure.php.net/manual/en/ref.pdo-oci.connection.php)
 *
 *  In the legacy dump mode:
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
        'dump' => null,
        'populator' => '',
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
        $this->connect();
    }

    public function _beforeSuite($settings = [])
    {
        if (!$this->config['populator']
            && $this->config['dump']
            &&  ($this->config['cleanup'] || ($this->config['populate']))
        ) {
            $this->readSql();
        }

        $this->connect();

        // starting with loading dump
        if ($this->config['populate']) {
            if ($this->config['cleanup']) {
                $this->cleanup();
            }
            $this->loadDump();
        }

        if ($this->config['reconnect']) {
            $this->disconnect();
        }
    }

    /**
     * Whether or not the db was populated with the dump file.
     * @return boolean True if the dump was loaded, false otherwise.
     */
    public function isPopulated()
    {
        return $this->populated;
    }

    private function readSql()
    {
        if (!file_exists(Configuration::projectDir() . $this->config['dump'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "\nFile with dump doesn't exist.\n"
                . "Please, check path for sql file: "
                . $this->config['dump']
            );
        }

        $sql = file_get_contents(Configuration::projectDir() . $this->config['dump']);

        // remove C-style comments (except MySQL directives)
        $sql = preg_replace('%/\*(?!!\d+).*?\*/%s', '', $sql);

        if (!empty($sql)) {
            // split SQL dump into lines
            $this->sql = preg_split('/\r\n|\n|\r/', $sql, -1, PREG_SPLIT_NO_EMPTY);
        }
    }

    private function connect()
    {
        try {
            $this->driver = Driver::create($this->config['dsn'], $this->config['user'], $this->config['password']);
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            if ($message === 'could not find driver') {
                list ($missingDriver, ) = explode(':', $this->config['dsn'], 2);
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

    public function _before(TestInterface $test)
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

    public function _after(TestInterface $test)
    {
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
                $this->debug("couldn't delete record " . json_encode($row['primary']) ." from {$row['table']}");
            }
        }
        $this->insertedRows = [];
        $this->populated = false;
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
            $this->populated = false;
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    protected function loadDump()
    {
        if ($this->config['populator']) {
            $this->loadDumpUsingPopulator();
        } else {
            $this->loadDumpUsingDriver();
        }
    }

    protected function loadDumpUsingPopulator()
    {
        if (!$this->config['dump']) {
            $this->debug("[Db] No dump file found. Skip loading the dump using the populator command.");
            return;
        }
        try {
            $populator = new DbPopulator($this->config['populator'], $this);
            $command = $populator->getBuiltCommand();
            $this->debug("[Db] Executing populator command: `$command`");
            list($result, $output, $exitCode) = $populator->execute();
            $this->debug("[Db] Done running populator command with result: `$result`");
            $this->debug("[Db] Exit code: `$exitCode`");
            $this->debug("[Db] ".count($output)." line/s of output:\n");
            foreach ($output as $l) {
                $this->debug("[Db] \t$l");
            }

            if (0 !== $exitCode) {
                throw new \RuntimeException(
                    implode(
                        "\n",
                        [
                            "The populator command did not end successfully: ",
                            "Exit code: $exitCode",
                            "Output:",
                            implode("\n", $output),
                        ]
                    )
                );
            }

            $this->populated = true;
        } catch (\Exception $e) {
            $this->debug(implode("\n", [get_class($e), $e->getMessage(), $e->getTraceAsString()]));
            throw new ModuleException(
                __CLASS__,
                implode(
                    "\n",
                    [
                        $e->getMessage(),
                        sprintf(
                            'Attempted to load the dump `%1$s` using the command `%2$s`',
                            $this->config['dump'],
                            $this->config['populator']
                        ),
                    ]
                )
            );
        }
    }

    protected function loadDumpUsingDriver()
    {
        if (!$this->sql) {
            return;
        }
        $this->driver->load($this->sql);
        $this->populated = true;
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
        $query = $this->driver->insert($table, $data);
        $parameters = array_values($data);
        $this->debugSection('Query', $query);
        $this->debugSection('Parameters', $parameters);
        $this->driver->executeQuery($query, $parameters);

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
                        throw new \InvalidArgumentException(
                            'Primary key field ' . $column . ' is not set for table ' . $table
                        );
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
        $this->assertGreaterThan(
            0,
            $res,
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

    public function dontSeeInDatabase($table, $criteria = [])
    {
        $count = $this->countInDatabase($table, $criteria);
        $this->assertLessThan(
            1,
            $count,
            'Unexpectedly found matching records for criteria ' . json_encode($criteria) . ' in table ' . $table
        );
    }

    /**
     * Count rows in a database
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
        $parameters = array_values($criteria);
        $this->debugSection('Query', $query);
        if (!empty($parameters)) {
            $this->debugSection('Parameters', $parameters);
        }
        $sth = $this->driver->executeQuery($query, $parameters);

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
}
