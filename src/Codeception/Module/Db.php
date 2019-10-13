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
use Codeception\Lib\Notification;
use Codeception\Util\ActionSequence;

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
 * [Check out the drivers](https://github.com/Codeception/Codeception/tree/2.4/src/Codeception/Lib/Driver)
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
 * * waitlock: 0 - wait lock (in seconds) that the database session should use for DDL statements
 * * ssl_key - path to the SSL key (MySQL specific, @see http://php.net/manual/de/ref.pdo-mysql.php#pdo.constants.mysql-attr-key)
 * * ssl_cert - path to the SSL certificate (MySQL specific, @see http://php.net/manual/de/ref.pdo-mysql.php#pdo.constants.mysql-attr-ssl-cert)
 * * ssl_ca - path to the SSL certificate authority (MySQL specific, @see http://php.net/manual/de/ref.pdo-mysql.php#pdo.constants.mysql-attr-ssl-ca)
 * * ssl_verify_server_cert - disables certificate CN verification (MySQL specific, @see http://php.net/manual/de/ref.pdo-mysql.php)
 * * ssl_cipher - list of one or more permissible ciphers to use for SSL encryption (MySQL specific, @see http://php.net/manual/de/ref.pdo-mysql.php#pdo.constants.mysql-attr-cipher)
 * * databases - include more database configs and switch between them in tests.
 * * initial_queries - list of queries to be executed right after connection to the database has been initiated, i.e. creating the database if it does not exist or preparing the database collation
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
 *              waitlock: 10
 *              ssl_key: '/path/to/client-key.pem'
 *              ssl_cert: '/path/to/client-cert.pem'
 *              ssl_ca: '/path/to/ca-cert.pem'
 *              ssl_verify_server_cert: false
 *              ssl_cipher: 'AES256-SHA'
 *              initial_queries:
 *                  - 'CREATE DATABASE IF NOT EXISTS temp_db;'
 *                  - 'USE temp_db;'
 *                  - 'SET NAMES utf8;'
 *
 * ## Example with multi-dumps
 *     modules:
 *          enabled:
 *             - Db:
 *                dsn: 'mysql:host=localhost;dbname=testdb'
 *                user: 'root'
 *                password: ''
 *                dump:
 *                   - 'tests/_data/dump.sql'
 *                   - 'tests/_data/dump-2.sql'
 *
 * ## Example with multi-databases
 *
 *     modules:
 *        enabled:
 *           - Db:
 *              dsn: 'mysql:host=localhost;dbname=testdb'
 *              user: 'root'
 *              password: ''
 *              databases:
 *                 db2:
 *                    dsn: 'mysql:host=localhost;dbname=testdb2'
 *                    user: 'userdb2'
 *                    password: ''
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
 * $I->seeInDatabase('users', ['name' => 'Davert', 'email' => 'davert@mail.com']);
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
 * $I->seeInDatabase('users', ['name' => 'Davert', 'email like' => 'davert%']);
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
     * @var array
     */
    protected $config = [
        'populate' => false,
        'cleanup' => false,
        'reconnect' => false,
        'waitlock' => 0,
        'dump' => null,
        'populator' => null,
    ];

    /**
     * @var array
     */
    protected $requiredFields = ['dsn', 'user', 'password'];
    const DEFAULT_DATABASE = 'default';

    /**
     * @var Driver[]
     */
    public $drivers = [];
    /**
     * @var \PDO[]
     */
    public $dbhs = [];
    public $databasesPopulated = [];
    public $databasesSql = [];
    protected $insertedRows = [];
    public $currentDatabase = self::DEFAULT_DATABASE;

    protected function getDatabases()
    {
        $databases = [$this->currentDatabase => $this->config];

        if (!empty($this->config['databases'])) {
            foreach ($this->config['databases'] as $databaseKey => $databaseConfig) {
                $databases[$databaseKey] = array_merge([
                    'populate' => false,
                    'cleanup' => false,
                    'reconnect' => false,
                    'waitlock' => 0,
                    'dump' => null,
                    'populator' => null,
                ], $databaseConfig);
            }
        }
        return $databases;
    }
    protected function connectToDatabases()
    {
        foreach ($this->getDatabases() as $databaseKey => $databaseConfig) {
            $this->connect($databaseKey, $databaseConfig);
        }
    }
    protected function cleanUpDatabases()
    {
        foreach ($this->getDatabases() as $databaseKey => $databaseConfig) {
            $this->_cleanup($databaseKey, $databaseConfig);
        }
    }
    protected function populateDatabases($configKey)
    {
        foreach ($this->getDatabases() as $databaseKey => $databaseConfig) {
            if ($databaseConfig[$configKey]) {
                if (!$databaseConfig['populate']) {
                    return;
                }

                if (isset($this->databasesPopulated[$databaseKey]) && $this->databasesPopulated[$databaseKey]) {
                    return;
                }
                $this->_loadDump($databaseKey, $databaseConfig);
            }
        }
    }
    protected function readSqlForDatabases()
    {
        foreach ($this->getDatabases() as $databaseKey => $databaseConfig) {
            $this->readSql($databaseKey, $databaseConfig);
        }
    }
    protected function removeInsertedForDatabases()
    {
        foreach ($this->getDatabases() as $databaseKey => $databaseConfig) {
            $this->amConnectedToDatabase($databaseKey);
            $this->removeInserted($databaseKey);
        }
    }
    protected function disconnectDatabases()
    {
        foreach ($this->getDatabases() as $databaseKey => $databaseConfig) {
            $this->disconnect($databaseKey);
        }
    }
    protected function reconnectDatabases()
    {
        foreach ($this->getDatabases() as $databaseKey => $databaseConfig) {
            if ($databaseConfig['reconnect']) {
                $this->disconnect($databaseKey);
                $this->connect($databaseKey, $databaseConfig);
            }
        }
    }

    public function __get($name)
    {
        Notification::deprecate("Properties dbh and driver are deprecated in favor of Db::_getDbh and Db::_getDriver", "Db module");

        if ($name == 'driver') {
            return $this->_getDriver();
        }
        if ($name == 'dbh') {
            return $this->_getDbh();
        }
    }

    /**
     * @return Driver
     */
    public function _getDriver()
    {
        return $this->drivers[$this->currentDatabase];
    }
    public function _getDbh()
    {
        return $this->dbhs[$this->currentDatabase];
    }

    /**
     * Make sure you are connected to the right database.
     *
     * ```php
     * <?php
     * $I->seeNumRecords(2, 'users');   //executed on default database
     * $I->amConnectedToDatabase('db_books');
     * $I->seeNumRecords(30, 'books');  //executed on db_books database
     * //All the next queries will be on db_books
     * ```
     * @param $databaseKey
     * @throws ModuleConfigException
     */
    public function amConnectedToDatabase($databaseKey)
    {
        if (empty($this->getDatabases()[$databaseKey]) && $databaseKey != self::DEFAULT_DATABASE) {
            throw new ModuleConfigException(
                __CLASS__,
                "\nNo database $databaseKey in the key databases.\n"
            );
        }
        $this->currentDatabase = $databaseKey;
    }

    /**
     * Can be used with a callback if you don't want to change the current database in your test.
     *
     * ```php
     * <?php
     * $I->seeNumRecords(2, 'users');   //executed on default database
     * $I->performInDatabase('db_books', function($I) {
     *     $I->seeNumRecords(30, 'books');  //executed on db_books database
     * });
     * $I->seeNumRecords(2, 'users');  //executed on default database
     * ```
     * List of actions can be pragmatically built using `Codeception\Util\ActionSequence`:
     *
     * ```php
     * <?php
     * $I->performInDatabase('db_books', ActionSequence::build()
     *     ->seeNumRecords(30, 'books')
     * );
     * ```
     * Alternatively an array can be used:
     *
     * ```php
     * $I->performInDatabase('db_books', ['seeNumRecords' => [30, 'books']]);
     * ```
     *
     * Choose the syntax you like the most and use it,
     *
     * Actions executed from array or ActionSequence will print debug output for actions, and adds an action name to
     * exception on failure.
     *
     * @param $databaseKey
     * @param \Codeception\Util\ActionSequence|array|callable $actions
     * @throws ModuleConfigException
     */
    public function performInDatabase($databaseKey, $actions)
    {
        $backupDatabase = $this->currentDatabase;
        $this->amConnectedToDatabase($databaseKey);

        if (is_callable($actions)) {
            $actions($this);
            $this->amConnectedToDatabase($backupDatabase);
            return;
        }
        if (is_array($actions)) {
            $actions = ActionSequence::build()->fromArray($actions);
        }

        if (!$actions instanceof ActionSequence) {
            throw new \InvalidArgumentException("2nd parameter, actions should be callback, ActionSequence or array");
        }

        $actions->run($this);
        $this->amConnectedToDatabase($backupDatabase);
    }

    public function _initialize()
    {
        $this->connectToDatabases();
    }

    public function __destruct()
    {
        $this->disconnectDatabases();
    }

    public function _beforeSuite($settings = [])
    {
        $this->readSqlForDatabases();
        $this->connectToDatabases();
        $this->cleanUpDatabases();
        $this->populateDatabases('populate');
    }

    private function readSql($databaseKey = null, $databaseConfig = null)
    {
        if ($databaseConfig['populator']) {
            return;
        }
        if (!$databaseConfig['cleanup'] && !$databaseConfig['populate']) {
            return;
        }
        if (empty($databaseConfig['dump'])) {
            return;
        }

        if (!is_array($databaseConfig['dump'])) {
            $databaseConfig['dump'] = [$databaseConfig['dump']];
        }

        $sql = '';

        foreach ($databaseConfig['dump'] as $filePath) {
            $sql .= $this->readSqlFile($filePath);
        }

        if (!empty($sql)) {
            // split SQL dump into lines
            $this->databasesSql[$databaseKey] = preg_split('/\r\n|\n|\r/', $sql, -1, PREG_SPLIT_NO_EMPTY);
        }
    }

    /**
     * @param $filePath
     *
     * @return bool|null|string|string[]
     * @throws \Codeception\Exception\ModuleConfigException
     */
    private function readSqlFile($filePath)
    {
        if (!file_exists(Configuration::projectDir() . $filePath)) {
            throw new ModuleConfigException(
                __CLASS__,
                "\nFile with dump doesn't exist.\n"
                . "Please, check path for sql file: "
                . $filePath
            );
        }

        $sql = file_get_contents(Configuration::projectDir() . $filePath);

        // remove C-style comments (except MySQL directives)
        $sql = preg_replace('%/\*(?!!\d+).*?\*/%s', '', $sql);

        return $sql;
    }

    private function connect($databaseKey, $databaseConfig)
    {
        if (!empty($this->drivers[$databaseKey]) && !empty($this->dbhs[$databaseKey])) {
            return;
        }
        $options = [];

        /**
         * @see http://php.net/manual/en/pdo.construct.php
         * @see http://php.net/manual/de/ref.pdo-mysql.php#pdo-mysql.constants
         */
        if (array_key_exists('ssl_key', $databaseConfig)
            && !empty($databaseConfig['ssl_key'])
            && defined('\PDO::MYSQL_ATTR_SSL_KEY')
        ) {
            $options[\PDO::MYSQL_ATTR_SSL_KEY] = (string) $databaseConfig['ssl_key'];
        }

        if (array_key_exists('ssl_cert', $databaseConfig)
            && !empty($databaseConfig['ssl_cert'])
            && defined('\PDO::MYSQL_ATTR_SSL_CERT')
        ) {
            $options[\PDO::MYSQL_ATTR_SSL_CERT] = (string) $databaseConfig['ssl_cert'];
        }

        if (array_key_exists('ssl_ca', $databaseConfig)
            && !empty($databaseConfig['ssl_ca'])
            && defined('\PDO::MYSQL_ATTR_SSL_CA')
        ) {
            $options[\PDO::MYSQL_ATTR_SSL_CA] = (string) $databaseConfig['ssl_ca'];
        }

        if (array_key_exists('ssl_cipher', $databaseConfig)
            && !empty($databaseConfig['ssl_cipher'])
            && defined('\PDO::MYSQL_ATTR_SSL_CIPHER')
        ) {
            $options[\PDO::MYSQL_ATTR_SSL_CIPHER] = (string) $databaseConfig['ssl_cipher'];
        }

        if (array_key_exists('ssl_verify_server_cert', $databaseConfig)
            && defined('\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')
        ) {
            $options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = (boolean) $databaseConfig[ 'ssl_verify_server_cert' ];
        }

        try {
            $this->debugSection('Connecting To Db', ['config' => $databaseConfig, 'options' => $options]);
            $this->drivers[$databaseKey] = Driver::create($databaseConfig['dsn'], $databaseConfig['user'], $databaseConfig['password'], $options);
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            if ($message === 'could not find driver') {
                list ($missingDriver, ) = explode(':', $databaseConfig['dsn'], 2);
                $message = "could not find $missingDriver driver";
            }

            throw new ModuleException(__CLASS__, $message . ' while creating PDO connection');
        }

        if ($databaseConfig['waitlock']) {
            $this->_getDriver()->setWaitLock($databaseConfig['waitlock']);
        }

        if (isset($databaseConfig['initial_queries'])) {
            foreach ($databaseConfig['initial_queries'] as $initialQuery) {
                $this->drivers[$databaseKey]->executeQuery($initialQuery, []);
            }
        }

        $this->debugSection('Db', 'Connected to ' . $databaseKey . ' ' . $this->drivers[$databaseKey]->getDb());
        $this->dbhs[$databaseKey] = $this->drivers[$databaseKey]->getDbh();
    }

    private function disconnect($databaseKey)
    {
        $this->debugSection('Db', 'Disconnected from ' . $databaseKey);
        $this->dbhs[$databaseKey] = null;
        $this->drivers[$databaseKey] = null;
    }

    public function _before(TestInterface $test)
    {
        $this->reconnectDatabases();
        $this->amConnectedToDatabase(self::DEFAULT_DATABASE);

        $this->cleanUpDatabases();

        $this->populateDatabases('cleanup');

        parent::_before($test);
    }

    public function _after(TestInterface $test)
    {
        $this->removeInsertedForDatabases();
        parent::_after($test);
    }

    protected function removeInserted($databaseKey = null)
    {
        $databaseKey = empty($databaseKey) ?  self::DEFAULT_DATABASE : $databaseKey;

        if (empty($this->insertedRows[$databaseKey])) {
            return;
        }

        foreach (array_reverse($this->insertedRows[$databaseKey]) as $row) {
            try {
                $this->_getDriver()->deleteQueryByCriteria($row['table'], $row['primary']);
            } catch (\Exception $e) {
                $this->debug("Couldn't delete record " . json_encode($row['primary']) ." from {$row['table']}");
            }
        }
        $this->insertedRows[$databaseKey] = [];
    }

    public function _cleanup($databaseKey = null, $databaseConfig = null)
    {
        $databaseKey = empty($databaseKey) ?  self::DEFAULT_DATABASE : $databaseKey;
        $databaseConfig = empty($databaseConfig) ?  $this->config : $databaseConfig;

        if (!$databaseConfig['populate']) {
            return;
        }
        if (!$databaseConfig['cleanup']) {
            return;
        }
        if (isset($this->databasesPopulated[$databaseKey]) && !$this->databasesPopulated[$databaseKey]) {
            return;
        }
        $dbh = $this->dbhs[$databaseKey];
        if (!$dbh) {
            throw new ModuleConfigException(
                __CLASS__,
                'No connection to database. Remove this module from config if you don\'t need database repopulation'
            );
        }
        try {
            if (false === $this->shouldCleanup($databaseConfig, $databaseKey)) {
                return;
            }
            $this->drivers[$databaseKey]->cleanup();
            $this->databasesPopulated[$databaseKey] = false;
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    /**
     * @param  array  $databaseConfig
     * @param  string $databaseKey
     * @return bool
     */
    protected function shouldCleanup($databaseConfig, $databaseKey)
    {
        // If using populator and it's not empty, clean up regardless
        if (!empty($databaseConfig['populator'])) {
            return true;
        }

        // If no sql dump for $databaseKey or sql dump is empty, don't clean up
        return !empty($this->databasesSql[$databaseKey]);
    }

    public function _isPopulated()
    {
        return $this->databasesPopulated[$this->currentDatabase];
    }

    public function _loadDump($databaseKey = null, $databaseConfig = null)
    {
        $databaseKey = empty($databaseKey) ?  self::DEFAULT_DATABASE : $databaseKey;
        $databaseConfig = empty($databaseConfig) ?  $this->config : $databaseConfig;

        if ($databaseConfig['populator']) {
            $this->loadDumpUsingPopulator($databaseKey, $databaseConfig);
            return;
        }
        $this->loadDumpUsingDriver($databaseKey);
    }

    protected function loadDumpUsingPopulator($databaseKey, $databaseConfig)
    {
        $populator = new DbPopulator($databaseConfig);
        $this->databasesPopulated[$databaseKey] = $populator->run();
    }

    protected function loadDumpUsingDriver($databaseKey)
    {
        if (!isset($this->databasesSql[$databaseKey])) {
            return;
        }
        if (!$this->databasesSql[$databaseKey]) {
            $this->debugSection('Db', 'No SQL loaded, loading dump skipped');
            return;
        }
        $this->drivers[$databaseKey]->load($this->databasesSql[$databaseKey]);
        $this->databasesPopulated[$databaseKey] = true;
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
        $query = $this->_getDriver()->insert($table, $data);
        $parameters = array_values($data);
        $this->debugSection('Query', $query);
        $this->debugSection('Parameters', $parameters);
        $this->_getDriver()->executeQuery($query, $parameters);

        try {
            $lastInsertId = (int)$this->_getDriver()->lastInsertId($table);
        } catch (\PDOException $e) {
            // ignore errors due to uncommon DB structure,
            // such as tables without _id_seq in PGSQL
            $lastInsertId = 0;
            $this->debugSection('DB error', $e->getMessage());
        }
        return $lastInsertId;
    }

    private function addInsertedRow($table, array $row, $id)
    {
        $primaryKey = $this->_getDriver()->getPrimaryKey($table);
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

        $this->insertedRows[$this->currentDatabase][] = [
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
     * @return mixed
     */
    protected function proceedSeeInDatabase($table, $column, $criteria)
    {
        $query = $this->_getDriver()->select($column, $table, $criteria);
        $parameters = array_values($criteria);
        $this->debugSection('Query', $query);
        if (!empty($parameters)) {
            $this->debugSection('Parameters', $parameters);
        }
        $sth = $this->_getDriver()->executeQuery($query, $parameters);

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
        $query      = $this->_getDriver()->select($column, $table, $criteria);
        $parameters = array_values($criteria);
        $this->debugSection('Query', $query);
        $this->debugSection('Parameters', $parameters);
        $sth = $this->_getDriver()->executeQuery($query, $parameters);

        return $sth->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    /**
     * Fetches a single column value from a database.
     * Provide table name, desired column and criteria.
     *
     * ``` php
     * <?php
     * $mail = $I->grabFromDatabase('users', 'email', array('name' => 'Davert'));
     * ```
     * Comparison expressions can be used as well:
     *
     * ```php
     * <?php
     * $post = $I->grabFromDatabase('posts', ['num_comments >=' => 100]);
     * $user = $I->grabFromDatabase('users', ['email like' => 'miles%']);
     * ```
     *
     * Supported operators: `<`, `>`, `>=`, `<=`, `!=`, `like`.
     *
     * @param string $table
     * @param string $column
     * @param array $criteria
     *
     * @return mixed Returns a single column value or false
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
        $query = $this->_getDriver()->update($table, $data, $criteria);
        $parameters = array_merge(array_values($data), array_values($criteria));
        $this->debugSection('Query', $query);
        if (!empty($parameters)) {
            $this->debugSection('Parameters', $parameters);
        }
        $this->_getDriver()->executeQuery($query, $parameters);
    }
}
