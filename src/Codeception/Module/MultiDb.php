<?php
namespace Codeception\Module;

use Codeception\Exception\TestRuntime as TestRuntimeException;
use Codeception\Lib\Driver\Db as Driver;
use Codeception\Exception\Module as ModuleException;
use Codeception\Exception\ModuleConfig as ModuleConfigException;
use Codeception\TestCase;
use Codeception\Util\SQL;

/**
 * MultiDb - Module that allows tests to perform setup queries and assertions across multiple databases.
 *
 * @author    Dino Korah <dino.korah@redmatter.com>
 * @copyright 2009-2014 Red Matter Ltd (UK)
 */
class MultiDb extends \Codeception\Module
{
    const ASIS_PREFIX = '@asis ';

    protected $dbh;

    protected $config = ['connectors'=>false];

    protected $requiredFields = ['connectors'];

    /** @var  Driver[] */
    protected $drivers = [];

    /** @var  Driver */
    protected $chosenDriver = null;

    protected $chosenConnector;

    protected $cleanup_actions = [];

    protected $connectorRequiredFields = ['dsn', 'user', 'password'];

    // @codingStandardsIgnoreStart overridden function from \Codeception\Module
    // HOOK: used after configuration is loaded
    public function _initialize()
    {
        $configOk = false;

        if (is_array($this->config['connectors'])) {
            foreach ($this->config['connectors'] as $connector => $connectorConfig) {
                if (is_array($connectorConfig)) {
                    $fields = array_keys($connectorConfig);
                    $configOk = (
                        array_intersect($this->connectorRequiredFields, $fields) == $this->connectorRequiredFields
                    );
                    if (!$configOk) {
                        break;
                    }
                }
            }
        }

        if (!$configOk) {
            throw new ModuleConfigException(
                __CLASS__,
                "\nOptions: " . implode(', ', $this->connectorRequiredFields) . " are required\n
                        Please, update the configuration and set all the required fields\n\n"
            );
        }

        parent::_initialize();
    }

    // HOOK: after scenario
    public function _after(TestCase $test)
    {
        foreach ($this->cleanup_actions as $cleanup_action) {
            call_user_func($cleanup_action, $this);
        }

        $this->cleanup_actions = [];

        parent::_after($test);
    }
    // @codingStandardsIgnoreEnd

    /**
     * @see MultiDb::amConnectedToDb
     *
     * @param string $connector
     *
     * @throws ModuleException
     * @return \Codeception\Lib\Driver\Db
     */
    private function getDriver($connector)
    {
        if (!(isset($this->drivers[$connector]) && is_object($this->drivers[$connector]))) {
            if (!isset($this->config['connectors'][$connector])) {
                throw new ModuleException(
                    __CLASS__,
                    "The specified connector, {$connector}, does not exist in the configuration"
                );
            }
            $config = $this->config['connectors'][$connector];

            try {
                $this->drivers[$connector] = Driver::create($config['dsn'], $config['user'], $config['password']);
            } catch (\PDOException $e) {
                throw new ModuleException(
                    __CLASS__,
                    $e->getMessage() . ' while creating PDO connection ['.get_class($e).']'
                );
            } catch (\Exception $e) {
                throw new ModuleException(
                    __CLASS__,
                    $e->getMessage() . ' while creating PDO connection ['.get_class($e).']'
                );
            }
        }

        return $this->drivers[$connector];
    }

    /**
     * Get the chosen driver or throw!
     *
     * @return Driver
     */
    private function getChosenDriver()
    {
        if (null === $this->chosenDriver) {
            throw new TestRuntimeException("No connector was chosen before interactions with Db");
        }

        return $this->chosenDriver;
    }

    /**
     * Connects the Guy to a database described by the named connector
     * See configuration for connector names
     *
     * @param $connector
     *
     * @throws ModuleException
     *
     * @return void
     */
    public function amConnectedToDb($connector)
    {
        $this->chosenDriver = $this->getDriver($connector);
        $this->chosenConnector = $connector;
    }

    /**
     * Insert a record into the given table
     *
     * @param string     $table                Table name, preferably Database.TableName
     * @param array      $field_values         An array of field values of the form ['Field1'=>Value, 'Field2'=>Value]
     * @param string     $pk_field             Field name to match the last-insert-id or primaryKeyValue
     * @param mixed      $pk_value             A value other than 0 or null which can identify the row to be cleaned-up
     * @param bool       $cleanup_after        If true, the inserted record will be cleaned up automatically when the
     *                                         run of a scenario is finished.
     * @param bool|array $ignore_dup_key boolean true to ignore duplicate-key error from database by using
     *                                         INSERT INTO ... ON DUPLICATE KEY UPDATE Field1=Value1, ...
     *                                         If you need more control, use an array to specify a list of fields
     *                                         from $field_values
     *
     * @return int
     */
    public function haveInDb(
        $table,
        $field_values,
        $pk_field = 'ID',
        $pk_value = null,
        $cleanup_after = true,
        $ignore_dup_key = true
    ) {
        $driver = $this->getChosenDriver();

        list($query, $params) = $this->formSqlInsert($table, $field_values, $pk_field, $ignore_dup_key);
        $this->debugSection('Query', $query);
        $this->debugSection('Params', $params);

        $statement = $driver->getDbh()->prepare($query);
        if (!$statement) {
            $this->fail("Query '$query' can't be executed.");
        }

        $res = $statement->execute($params);
        if (!$res) {
            $this->fail(sprintf("Record with %s couldn't be inserted into %s", json_encode($field_values), $table));
        }

        $last_insert_id = 0;

        // For some reason we are not seeing the default value of NULL reaching here, if not specified in the test code
        if (!$pk_value) {
            try {
                $last_insert_id = (int) $driver->lastInsertId($table);
            } catch (\PDOException $e) {
                // ignore errors due to uncommon DB structure, such as tables without auto-inc
            }
        } else {
            $last_insert_id = $pk_value;
        }

        if ($cleanup_after && $last_insert_id) {
            $this->setupCleanup(SQL\CleanupAction::delete($table, [$pk_field => $last_insert_id]));
        }

        return $last_insert_id;
    }

    /**
     * Update a table with values for rows matching the given where clause
     *
     * @param string $table         Table name, preferably Database.TableName
     * @param array  $criteria      and array of field values of the form ['Field1'=>Value, 'Field2'=>Value] which gets
     *                              converted to "Field1=Value AND Field2=Value"
     * @param array  $field_updates and array of field values of the form ['Field1'=>Value, 'Field2'=>Value] which will
     *                              describe the new values for the given fields
     *
     * @return void
     */
    public function haveUpdatedDb($table, $field_updates, $criteria)
    {
        $driver = $this->getChosenDriver();

        list($query, $params) = $this->formSqlUpdate($table, $criteria, $field_updates);
        $this->debugSection('Query', $query);
        $this->debugSection('Params', $params);

        $statement = $driver->getDbh()->prepare($query);
        if (!$statement) {
            $this->fail("Query '$query' can't be executed.");
        }

        $res = $statement->execute($params);
        if (!$res) {
            $this->fail(
                sprintf(
                    "Record selected with %s couldn't be updated with %s into %s",
                    json_encode($criteria),
                    json_encode($field_updates),
                    $table
                )
            );
        }
    }

    /**
     * See in Db if there are records that match the given criteria in the given table.
     *
     * @param string $table          Table name, preferably Database.TableName
     * @param array  $criteria       Row selection criteria of the form ['Field1'=>Value, 'Field2'=>Value] which gets
     *                               converted to "Field1=Value AND Field2=Value"
     * @param int $count_expected    Expected record count; You can also specify the number of records that you expect
     *                               to see or you can use the default value of -1 to specify "any number of records".
     *                               Use -1 if you do not care how many records are present in the table, that matches
     *                               the given criteria, as long as at least one is found.
     */
    public function seeInDb($table, $criteria, $count_expected = -1)
    {
        $driver = $this->getChosenDriver();

        list($query, $params) = $this->formSqlSelect($table, $criteria, [new SQL\AsIs('COUNT(*)')]);
        $this->debugSection('Query', $query);
        $this->debugSection('Params', $params);

        $statement = $driver->getDbh()->prepare($query);
        if (!$statement) {
            $this->fail("Query '$query' can't be executed.");
        }

        $res = $statement->execute($params);
        if (!$res) {
            $this->fail(
                sprintf(
                    "Record selected with %s couldn't be counted from table %s",
                    json_encode($criteria),
                    $table
                )
            );
        }

        $count = $statement->fetchColumn(0);
        if ($count_expected < 0) {
            $this->assertGreaterThan(0, $count, 'No matching records found');
        } elseif ($count_expected == 0) {
            $this->assertLessThan(1, $count, 'Matching records were found');
        } else {
            $this->assertEquals($count_expected, $count, 'No given number of matching records found');
        }
    }

    /**
     * Same as @see seeInDb except that the count specified here is 0
     *
     * @param string $table    Table name, preferably Database.TableName
     * @param array  $criteria Row selection criteria of the form ['Field1'=>Value, 'Field2'=>Value] which gets
     *                         converted to "Field1=Value AND Field2=Value"
     */
    public function dontSeeInDb($table, $criteria)
    {
        $this->seeInDb($table, $criteria, 0);
    }

    /**
     * Get records from the table that match the criteria
     *
     * @param string       $table        Table name, preferably Database.TableName
     * @param array        $criteria     Row selection criteria of the form ['Field1'=>Value, 'Field2'=>Value] which
     *                                   gets converted to "Field1=Value AND Field2=Value"
     * @param array|string $fields       It can be a free formed SQL fragment to describe the values to select or an
     *                                   array of the form ['Field1', 'Field2']
     * @param array        $fetchPdoArgs Options to be passed to PDOStatement::fetchAll,
     *                                   see http://php.net/manual/en/pdostatement.fetchall.php
     *
     * @return array an array of rows ( depending on the $fetchPdoArgs given )
     */
    public function getFromDb($table, $criteria, $fields = null, $fetchPdoArgs = array(\PDO::FETCH_ASSOC))
    {
        $driver = $this->getChosenDriver();

        list($query, $params) = $this->formSqlSelect($table, $criteria, $fields);
        $this->debugSection('Query', $query);
        $this->debugSection('Params', $params);

        $statement = $driver->getDbh()->prepare($query);
        if (!$statement) {
            $this->fail("Query '$query' can't be executed.");
        }

        $res = $statement->execute($params);
        if (!$res) {
            $this->fail(
                sprintf(
                    "Record with %s columns couldn't be selected with %s from table %s",
                    json_encode($fields),
                    json_encode($criteria),
                    $table
                )
            );
        }

        return call_user_func_array([$statement, 'fetchAll'], $fetchPdoArgs);
    }

    /**
     * Delete from a table with values for rows matching the given criteria
     *
     * @param string $table    Table name, preferably Database.TableName
     * @param array  $criteria Array of field values of the form ['Field1'=>Value, 'Field2'=>Value] which gets
     *                         converted to "Field1=Value AND Field2=Value"
     *
     * @return void
     */
    public function haveDeletedFromDb($table, $criteria)
    {
        $driver = $this->getChosenDriver();

        list($query, $params) = $this->formSqlDelete($table, $criteria);
        $this->debugSection('Query', $query);
        $this->debugSection('Params', $params);

        $statement = $driver->getDbh()->prepare($query);
        if (!$statement) {
            $this->fail("Query '$query' can't be executed.");
        }

        $res = $statement->execute($params);
        if (!$res) {
            $this->fail(
                sprintf(
                    "Record couldn't be deleted with %s from table %s",
                    json_encode($criteria),
                    $table
                )
            );
        }
    }

    /**
     * executes the given SQL
     *
     * @param string $query        an SQL optionally with ? for parameters specified in $params
     * @param array  $params       If $query is parametrised with ?, then this array should have the values for them
     * @param array  $fetchPdoArgs Options to be passed to PDOStatement::fetchAll,
     *                             see http://php.net/manual/en/pdostatement.fetchall.php
     *
     * @return mixed row count for non-SELECT query and an array of rows ( depending on the $fetchPdoArgs given )
     */
    public function executeSql($query, $params = array(), $fetchPdoArgs = array(\PDO::FETCH_ASSOC))
    {
        $driver = $this->getChosenDriver();

        $this->debugSection('Query', $query);
        $this->debugSection('Params', $params);

        $statement = $driver->getDbh()->prepare($query);
        if (!$statement) {
            $this->fail("Query '$query' can't be executed.");
        }

        $res = $statement->execute($params);
        if (!$res) {
            $this->fail(
                sprintf(
                    "Query %s couldn't be run with the params %s",
                    $query,
                    json_encode($params)
                )
            );
        }

        // if not a SELECT query, then return the affected rows
        if (0 == $statement->columnCount()) {
            return $statement->rowCount();
        }

        return call_user_func_array([$statement, 'fetchAll'], $fetchPdoArgs);
    }

    /**
     * Setup cleanup
     *
     * @param SQL\CleanupAction $cleanup_action
     *
     * @return void
     */
    public function setupCleanup(SQL\CleanupAction $cleanup_action)
    {
        $cleanup_action->setConnector($this->chosenConnector);
        array_unshift($this->cleanup_actions, $cleanup_action);
    }

    /**
     * if the value starts with "@asis " it will be interpreted as SQL\AsIs
     *
     * @param string|SQL\AsIs &$value value to be normalised
     *
     * @return bool true if it was normalised
     */
    private static function normaliseAsIs(&$value)
    {
        if (is_scalar($value) && 0 === stripos($value, self::ASIS_PREFIX)) {
            $value = new SQL\AsIs(substr($value, strlen(self::ASIS_PREFIX)));
            return true;
        }

        return false;
    }

    /**
     * Normalise params list for easy processing later on
     *
     * @param array $params params array from one of the public functions
     *
     * @return array [ [ field, placeholder, value ], ... ]
     */
    protected static function normaliseParameterList($params)
    {
        $toScalar = function ($value) {
            return (null === $value)? $value: (string)$value;
        };

        array_walk(
            $params,
            function (&$value, $field) use ($toScalar) {
                self::normaliseAsIs($value);

                // Check if no field was specified (so the array index will be an integer).
                if (is_numeric($field)) {
                    $value = ($value instanceof SQL\AsIs)?
                        array(null, null, $toScalar($value)) : array(null, '?', $toScalar($value));
                } else {
                    $value = ($value instanceof SQL\AsIs)?
                        array($field, null, $toScalar($value)) : array($field, '?', $toScalar($value));
                }
            }
        );

        return array_values($params);
    }

    /**
     * Forms the INSERT SQL string to feed the database
     *
     * @param string     $table                table name
     * @param array      $data                 data to insert
     * @param string     $pk_field             primary key field
     * @param bool|array $ignore_duplicate_key true to append the ON DUPLICATE KEY syntax
     *
     * @return array of the sql query and params list
     */
    private function formSqlInsert($table, array $data, $pk_field, $ignore_duplicate_key)
    {
        $driver = $this->getChosenDriver();

        // assumes that no one would want some crazy SQL formed from the given $data
        $columns = array_map(array($driver, 'getQuotedName'), array_keys($data));

        $ignore_duplicate_key_sql = null;
        // either true or an array containing field names
        if ($ignore_duplicate_key && (!is_array($ignore_duplicate_key) || count($ignore_duplicate_key))) {
            $update_fields = array_filter(
                is_array($ignore_duplicate_key)? $ignore_duplicate_key: array_keys($data),
                function ($field) use ($pk_field) {
                    return $field != $pk_field;
                }
            );
            $ignore_duplicate_key_sql = ' ON DUPLICATE KEY UPDATE '.
                implode(
                    ', ',
                    array(
                        sprintf(
                            '%s=LAST_INSERT_ID(%s)',
                            $driver->getQuotedName($pk_field),
                            $driver->getQuotedName($pk_field)
                        )
                    ) + array_map(
                        function ($field) use ($driver) {
                            return $driver->getQuotedName($field).'=VALUES('.$field.')';
                        },
                        $update_fields
                    )
                );
        }

        $params = self::normaliseParameterList($data);

        $param_list = array();
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s) %s",
            $driver->getQuotedName($table),
            implode(', ', $columns),
            implode(
                ', ',
                array_map(
                    function ($value) use (&$param_list) {

                        // $field = $value[0];
                        $placeholder = $value[1];
                        $value = $value[2];

                        if ($placeholder !== null) {
                            $param_list[] = $value;
                            return $placeholder;
                        }

                        return $value;
                    },
                    $params
                )
            ),
            $ignore_duplicate_key_sql
        );

        return [$sql, $param_list];
    }

    /**
     * Prepares a fragment of SQL to be used within a query.
     * This supports both [field] = [value]|[placeholder] or just [value]|[placeholder]
     *
     * @param string $field       The field name to be used, set to null if it's not needed
     * @param string $placeholder The placeholder name to be used, set to null if a value is specified instead
     * @param string $value       The value to be used, set to null if a placeholder is specified instead
     * @param Driver $driver      Database driver, used to quote field names
     *
     * @return string
     */
    private static function prepareClause($field, $placeholder, $value, Driver $driver)
    {
        $rhs = ($placeholder === null)? $value : $placeholder;

        if ($field === null) {
            return $rhs;
        }

        // If the value is NULL, we need to use IS NULL, rather than =.
        $operator = ($value === null? 'IS': '=');
        return "{$driver->getQuotedName($field)} {$operator} {$rhs}";
    }

    /**
     * Forms the UPDATE SQL string to feed the database
     *
     * @param string $table         table name
     * @param array  $criteria      criteria for the update
     * @param array  $fields_update updates for the records that match criteria
     *
     * @return array of the sql query and params list
     */
    private function formSqlUpdate($table, array $criteria, array $fields_update)
    {
        $driver = $this->getChosenDriver();

        $where_params = self::normaliseParameterList($criteria);
        $update_params = self::normaliseParameterList($fields_update);

        $param_list = array();
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $driver->getQuotedName($table),
            implode(
                ', ',
                array_map(
                    function ($value) use (&$param_list, $driver) {

                        $field = $value[0];
                        $placeholder = $value[1];
                        $value = $value[2];

                        if ($placeholder !== null) {
                            $param_list[] = $value;
                        }

                        return self::prepareClause($field, $placeholder, $value, $driver);
                    },
                    $update_params
                )
            ),
            implode(
                ' AND ',
                array_map(
                    function ($value) use (&$param_list, $driver) {

                        $field = $value[0];
                        $placeholder = $value[1];
                        $value = $value[2];

                        if ($placeholder !== null) {
                            $param_list[] = $value;
                        }

                        return self::prepareClause($field, $placeholder, $value, $driver);
                    },
                    $where_params
                )
            )
        );

        return [$sql, $param_list];
    }

    /**
     * Forms the SELECT SQL string to feed the database
     *
     * @param string $table    table name
     * @param array  $criteria criteria for selecting records
     * @param array  $columns  columns ( or As Is expression ) to select
     *
     * @return array of the sql query and params list
     */
    private function formSqlSelect($table, $criteria, $columns = null)
    {
        $driver = $this->getChosenDriver();

        $criteriaParams = self::normaliseParameterList($criteria);

        if (!$columns) {
            $columns = [new SQL\AsIs('*')];
        } elseif (is_scalar($columns)) {
            $columns = [new SQL\AsIs($columns)];
        }

        $param_list = array();
        $sql = sprintf(
            "SELECT %s FROM %s WHERE %s",
            implode(
                ', ',
                array_map(
                    function ($column) use (&$param_list, $driver) {
                        self::normaliseAsIs($column);
                        if ($column instanceof SQL\AsIs) {
                            return (string)$column;
                        }

                        return $driver->getQuotedName($column);
                    },
                    $columns
                )
            ),
            $driver->getQuotedName($table),
            implode(
                ' AND ',
                array_map(
                    function ($value) use (&$param_list, $driver) {

                        $field = $value[0];
                        $placeholder = $value[1];
                        $value = $value[2];

                        if ($placeholder !== null) {
                            $param_list[] = $value;
                        }

                        return self::prepareClause($field, $placeholder, $value, $driver);
                    },
                    $criteriaParams
                )
            )
        );

        return [$sql, $param_list];
    }

    /**
     * Forms the DELETE SQL string to feed the database
     *
     * @param string $table    table name
     * @param array  $criteria criteria for deleting records
     *
     * @return array of the sql query and params list
     */
    private function formSqlDelete($table, $criteria)
    {
        $driver = $this->getChosenDriver();

        $where_params = self::normaliseParameterList($criteria);

        $param_list = array();
        $sql = sprintf(
            "DELETE FROM %s WHERE %s",
            $driver->getQuotedName($table),
            implode(
                ' AND ',
                array_map(
                    function ($value) use (&$param_list, $driver) {

                        $field = $value[0];
                        $placeholder = $value[1];
                        $value = $value[2];

                        if ($placeholder !== null) {
                            $param_list[] = $value;
                        }

                        return self::prepareClause($field, $placeholder, $value, $driver);
                    },
                    $where_params
                )
            )
        );

        return [$sql, $param_list];
    }
}
