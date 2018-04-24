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
 * Access a database.
 *
 * The most important function of this module is to clean a database before each test.
 * This module also provides actions to perform checks in a database, e.g. [seeInDatabase()](http://codeception.com/docs/modules/Db#seeInDatabase)
 *
 * In order to have your database populated with data you need a raw SQL dump.
 * Simply put the dump in the `tests/_data` directory (by default) and specify the path in the config.
 * The next time after the database is cleared, all your data will be restored from the dump.
 * Don't forget to include `CREATE TABLE` statements in the dump.
 *
 * Supported and tested databases are:
 *
 * * MySQL
 * * SQLite (i.e. just one file)
 * * PostgreSQL
 *
 * Also available:
 *
 * * MS SQL
 * * Oracle
 *
 * Connection is done by database Drivers, which are stored in the `Codeception\Lib\Driver` namespace.
 * [Check out the drivers](https://github.com/Codeception/Codeception/tree/2.3/src/Codeception/Lib/Driver)
 * if you run into problems loading dumps and cleaning databases.
 *
 * ## Config
 *
 * * dsn *required* - PDO DSN
 * * user *required* - username to access database
 * * password *required* - password
 * * dump - path to database dump
 * * populate: false - whether the the dump should be loaded before the test suite is started
 * * cleanup: false - whether the dump should be reloaded before each test
 * * reconnect: false - whether the module should reconnect to the database before each test
 * * ssl_key - path to the SSL key (MySQL specific, @see http://php.net/manual/de/ref.pdo-mysql.php#pdo.constants.mysql-attr-key)
 * * ssl_cert - path to the SSL certificate (MySQL specific, @see http://php.net/manual/de/ref.pdo-mysql.php#pdo.constants.mysql-attr-ssl-cert)
 * * ssl_ca - path to the SSL certificate authority (MySQL specific, @see http://php.net/manual/de/ref.pdo-mysql.php#pdo.constants.mysql-attr-ssl-ca)
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
 *              cleanup: true
 *              reconnect: true
 *              ssl_key: '/path/to/client-key.pem'
 *              ssl_cert: '/path/to/client-cert.pem'
 *              ssl_ca: '/path/to/ca-cert.pem'
 *
 * ## SQL data dump
 *
 * There are two ways of loading the dump into your database:
 *
 * ### Populator
 *
 * The recommended approach is to configure a `populator`, an external command to load a dump. Command parameters like host, username, password, database
 * can be obtained from the config and inserted into placeholders:
 *
 * For MySQL:
 *
 * ```yaml
 * modules:
 *    enabled:
 *       - Db:
 *          dsn: 'mysql:host=localhost;dbname=testdb'
 *          user: 'root'
 *          password: ''
 *          dump: 'tests/_data/dump.sql'
 *          populate: true # run populator before all tests
 *          cleanup: true # run populator before each test
 *          populator: 'mysql -u $user -h $host $dbname < $dump'
 * ```
 *
 * For PostgreSQL (using pg_restore)
 *
 * ```
 * modules:
 *    enabled:
 *       - Db:
 *          dsn: 'pgsql:host=localhost;dbname=testdb'
 *          user: 'root'
 *          password: ''
 *          dump: 'tests/_data/db_backup.dump'
 *          populate: true # run populator before all tests
 *          cleanup: true # run populator before each test
 *          populator: 'pg_restore -u $user -h $host -D $dbname < $dump'
 * ```
 *
 *  Variable names are being taken from config and DSN which has a `keyword=value` format, so you should expect to have a variable named as the
 *  keyword with the full value inside it.
 *
 *  PDO dsn elements for the supported drivers:
 *  * MySQL: [PDO_MYSQL DSN](https://secure.php.net/manual/en/ref.pdo-mysql.connection.php)
 *  * SQLite: [PDO_SQLITE DSN](https://secure.php.net/manual/en/ref.pdo-sqlite.connection.php)
 *  * PostgreSQL: [PDO_PGSQL DSN](https://secure.php.net/manual/en/ref.pdo-pgsql.connection.php)
 *  * MSSQL: [PDO_SQLSRV DSN](https://secure.php.net/manual/en/ref.pdo-sqlsrv.connection.php)
 *  * Oracle: [PDO_OCI DSN](https://secure.php.net/manual/en/ref.pdo-oci.connection.php)
 *
 * ### Dump
 *
 * Db module by itself can load SQL dump without external tools by using current database connection.
 * This approach is system-independent, however, it is slower than using a populator and may have parsing issues (see below).
 *
 * Provide a path to SQL file in `dump` config option:
 *
 * ```yaml
 * modules:
 *    enabled:
 *       - Db:
 *          dsn: 'mysql:host=localhost;dbname=testdb'
 *          user: 'root'
 *          password: ''
 *          populate: true # load dump before all tests
 *          cleanup: true # load dump for each test
 *          dump: 'tests/_data/dump.sql'
 * ```
 *
 *  To parse SQL Db file, it should follow this specification:
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
 * `seeInDatabase`, `dontSeeInDatabase`, `seeNumRecords`, `grabFromDatabase` and `grabNumRecords` methods
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
 * Since version 2.1.9 it's possible to use LIKE in a condition, as shown here:
 *
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
        'populate' => false,
        'cleanup' => false,
        'reconnect' => false,
        'dump' => null,
        'populator' => null,
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
                $this->_cleanup();
            }
            $this->_loadDump();
        }

        if ($this->config['reconnect']) {
            $this->disconnect();
        }
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
        $options = [];
 
        /**
         * @see http://php.net/manual/en/pdo.construct.php
         * @see http://php.net/manual/de/ref.pdo-mysql.php#pdo-mysql.constants
         */
        if (array_key_exists('ssl_key', $this->config) && !empty($this->config['ssl_key'])) {
            $options[\PDO::MYSQL_ATTR_SSL_KEY] = $this->config['ssl_key'];
        }
 
        if (array_key_exists('ssl_cert', $this->config) && !empty($this->config['ssl_cert'])) {
            $options[\PDO::MYSQL_ATTR_SSL_CERT] = $this->config['ssl_cert'];
        }
 
        if (array_key_exists('ssl_ca', $this->config) && !empty($this->config['ssl_ca'])) {
            $options[\PDO::MYSQL_ATTR_SSL_CA] = $this->config['ssl_ca'];
        }

        try {
            $this->driver = Driver::create($this->config['dsn'], $this->config['user'], $this->config['password'], $options);
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            if ($message === 'could not find driver') {
                list ($missingDriver, ) = explode(':', $this->config['dsn'], 2);
                $message = "could not find $missingDriver driver";
            }

            throw new ModuleException(__CLASS__, $message . ' while creating PDO connection');
        }
        $this->debugSection('Db', 'Connected to ' . $this->driver->getDb());
        $this->dbh = $this->driver->getDbh();
    }

    private function disconnect()
    {
        $this->debugSection('Db', 'Disconnected');
        $this->dbh = null;
        $this->driver = null;
    }

    public function _before(TestInterface $test)
    {
        if ($this->config['reconnect']) {
            $this->connect();
        }
        if ($this->config['cleanup'] && !$this->populated) {
            $this->_cleanup();
            $this->_loadDump();
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

    public function _cleanup()
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

    public function isPopulated()
    {
        return $this->populated;
    }

    public function _loadDump()
    {
        if ($this->config['populator']) {
            $this->loadDumpUsingPopulator();
            return;
        }
        $this->loadDumpUsingDriver();
    }

    protected function loadDumpUsingPopulator()
    {
        $populator = new DbPopulator($this->config);
        $this->populated = $populator->run();
    }

    protected function loadDumpUsingDriver()
    {
        if (!$this->sql) {
            $this->debugSection('Db', 'No SQL loaded, loading dump skipped');
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
        $lastInsertId = $this->_insertInDatabase($table, $data);

        $this->addInsertedRow($table, $data, $lastInsertId);

        return $lastInsertId;
    }
    
    public function _insertInDatabase($table, array $data)
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

    /**
     * Fetches all values from the column in database.
     * Provide table name, desired column and criteria.
     *
     * @param string $table
     * @param string $column
     * @param array  $criteria
     *
     * @return array
     */
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

    /**
     * Fetches all values from the column in database.
     * Provide table name, desired column and criteria.
     *
     * ``` php
     * <?php
     * $mails = $I->grabColumnFromDatabase('users', 'email', array('name' => 'RebOOter'));
     * ```
     *
     * @param string $table
     * @param string $column
     * @param array $criteria
     *
     * @return array
     */
    public function grabColumnFromDatabase($table, $column, array $criteria = [])
    {
        $query      = $this->driver->select($column, $table, $criteria);
        $parameters = array_values($criteria);
        $this->debugSection('Query', $query);
        $this->debugSection('Parameters', $parameters);
        $sth = $this->driver->executeQuery($query, $parameters);
        
        return $sth->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    /**
     * Fetches all values from the column in database.
     * Provide table name, desired column and criteria.
     *
     * ``` php
     * <?php
     * $mails = $I->grabFromDatabase('users', 'email', array('name' => 'RebOOter'));
     * ```
     *
     * @param string $table
     * @param string $column
     * @param array  $criteria
     *
     * @return array
     */
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
     * Update an SQL record into a database.
     *
     * ```php
     * <?php
     * $I->updateInDatabase('users', array('isAdmin' => true), array('email' => 'miles@davis.com'));
     * ?>
     * ```
     *
     * @param string $table
     * @param array $data
     * @param array $criteria
     */
    public function updateInDatabase($table, array $data, array $criteria = [])
    {
        $query = $this->driver->update($table, $data, $criteria);
        $parameters = array_merge(array_values($data), array_values($criteria));
        $this->debugSection('Query', $query);
        if (!empty($parameters)) {
            $this->debugSection('Parameters', $parameters);
        }
        $this->driver->executeQuery($query, $parameters);
    }
}
