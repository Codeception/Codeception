<?php

namespace Codeception\Lib\Driver;

use Codeception\Exception\ModuleException;

class Db
{
    /**
     * @var \PDO
     */
    protected $dbh;

    /**
     * @var string
     */
    protected $dsn;

    protected $user;
    protected $password;

    /**
     * @var array
     *
     * @see http://php.net/manual/de/pdo.construct.php
     */
    protected $options;

    /**
     * associative array with table name => primary-key
     *
     * @var array
     */
    protected $primaryKeys = [];

    public static function connect($dsn, $user, $password, $options = null)
    {
        $dbh = new \PDO($dsn, $user, $password, $options);
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $dbh;
    }

    /**
     * @static
     *
     * @param $dsn
     * @param $user
     * @param $password
     * @param [optional] $options
     *
     * @see http://php.net/manual/en/pdo.construct.php
     * @see http://php.net/manual/de/ref.pdo-mysql.php#pdo-mysql.constants
     *
     * @return Db|SqlSrv|MySql|Oci|PostgreSql|Sqlite
     */
    public static function create($dsn, $user, $password, $options = null)
    {
        $provider = self::getProvider($dsn);

        switch ($provider) {
            case 'sqlite':
                return new Sqlite($dsn, $user, $password, $options);
            case 'mysql':
                return new MySql($dsn, $user, $password, $options);
            case 'pgsql':
                return new PostgreSql($dsn, $user, $password, $options);
            case 'mssql':
            case 'dblib':
            case 'sqlsrv':
                return new SqlSrv($dsn, $user, $password, $options);
            case 'oci':
                return new Oci($dsn, $user, $password, $options);
            default:
                return new Db($dsn, $user, $password, $options);
        }
    }

    public static function getProvider($dsn)
    {
        return substr($dsn, 0, strpos($dsn, ':'));
    }

    /**
     * @param $dsn
     * @param $user
     * @param $password
     * @param [optional] $options
     *
     * @see http://php.net/manual/en/pdo.construct.php
     * @see http://php.net/manual/de/ref.pdo-mysql.php#pdo-mysql.constants
     */
    public function __construct($dsn, $user, $password, $options = null)
    {
        $this->dbh = new \PDO($dsn, $user, $password, $options);
        $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->dsn = $dsn;
        $this->user = $user;
        $this->password = $password;
        $this->options = $options;
    }

    public function __destruct()
    {
        if ($this->dbh->inTransaction()) {
            $this->dbh->rollBack();
        }
        $this->dbh = null;
    }

    public function getDbh()
    {
        return $this->dbh;
    }

    public function getDb()
    {
        $matches = [];
        $matched = preg_match('~dbname=(\w+)~s', $this->dsn, $matches);
        if (!$matched) {
            return false;
        }

        return $matches[1];
    }

    public function cleanup()
    {
    }

    /**
     * Set the lock waiting interval for the database session
     * @param int $seconds
     * @return void
     */
    public function setWaitLock($seconds)
    {
    }

    public function load($sql)
    {
        $query = '';
        $delimiter = ';';
        $delimiterLength = 1;

        foreach ($sql as $sqlLine) {
            if (preg_match('/DELIMITER ([\;\$\|\\\\]+)/i', $sqlLine, $match)) {
                $delimiter = $match[1];
                $delimiterLength = strlen($delimiter);
                continue;
            }

            $parsed = $this->sqlLine($sqlLine);
            if ($parsed) {
                continue;
            }

            $query .= "\n" . rtrim($sqlLine);

            if (substr($query, -1 * $delimiterLength, $delimiterLength) == $delimiter) {
                $this->sqlQuery(substr($query, 0, -1 * $delimiterLength));
                $query = '';
            }
        }

        if ($query !== '') {
            $this->sqlQuery($query);
        }
    }

    public function insert($tableName, array &$data)
    {
        $columns = array_map(
            [$this, 'getQuotedName'],
            array_keys($data)
        );

        return sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->getQuotedName($tableName),
            implode(', ', $columns),
            implode(', ', array_fill(0, count($data), '?'))
        );
    }

    public function select($column, $table, array &$criteria)
    {
        $where = $this->generateWhereClause($criteria);

        $query = "SELECT %s FROM %s %s";
        return sprintf($query, $column, $this->getQuotedName($table), $where);
    }

    private function getSupportedOperators()
    {
        return [
            'like',
            '!=',
            '<=',
            '>=',
            '<',
            '>',
        ];
    }

    protected function generateWhereClause(array &$criteria)
    {
        if (empty($criteria)) {
            return '';
        }

        $operands = $this->getSupportedOperators();

        $params = [];
        foreach ($criteria as $k => $v) {
            if ($v === null) {
                $params[] = $this->getQuotedName($k) . " IS NULL ";
                unset($criteria[$k]);
                continue;
            }

            $hasOperand = false; // search for equals - no additional operand given

            foreach ($operands as $operand) {
                if (!stripos($k, " $operand") > 0) {
                    continue;
                }

                $hasOperand = true;
                $k = str_ireplace(" $operand", '', $k);
                $operand = strtoupper($operand);
                $params[] = $this->getQuotedName($k) . " $operand ? ";
                break;
            }

            if (!$hasOperand) {
                $params[] = $this->getQuotedName($k) . " = ? ";
            }
        }

        return 'WHERE ' . implode('AND ', $params);
    }

    /**
     * @deprecated use deleteQueryByCriteria instead
     */
    public function deleteQuery($table, $id, $primaryKey = 'id')
    {
        $query = 'DELETE FROM ' . $this->getQuotedName($table) . ' WHERE ' . $this->getQuotedName($primaryKey) . ' = ?';
        $this->executeQuery($query, [$id]);
    }

    public function deleteQueryByCriteria($table, array $criteria)
    {
        $where = $this->generateWhereClause($criteria);

        $query = 'DELETE FROM ' . $this->getQuotedName($table) . ' ' . $where;
        $this->executeQuery($query, array_values($criteria));
    }

    public function lastInsertId($table)
    {
        return $this->getDbh()->lastInsertId();
    }

    public function getQuotedName($name)
    {
        return '"' . str_replace('.', '"."', $name) . '"';
    }

    protected function sqlLine($sql)
    {
        $sql = trim($sql);
        return (
            $sql === ''
            || $sql === ';'
            || preg_match('~^((--.*?)|(#))~s', $sql)
        );
    }

    protected function sqlQuery($query)
    {
        try {
            $this->dbh->exec($query);
        } catch (\PDOException $e) {
            throw new ModuleException(
                'Codeception\Module\Db',
                $e->getMessage() . "\nSQL query being executed: " . $query
            );
        }
    }

    public function executeQuery($query, array $params)
    {
        $sth = $this->dbh->prepare($query);
        if (!$sth) {
            throw new \Exception("Query '$query' can't be prepared.");
        }

        $i = 0;
        foreach ($params as $value) {
            $i++;
            if (is_bool($value)) {
                $type = \PDO::PARAM_BOOL;
            } elseif (is_int($value)) {
                $type = \PDO::PARAM_INT;
            } else {
                $type = \PDO::PARAM_STR;
            }
            $sth->bindValue($i, $value, $type);
        }

        $sth->execute();
        return $sth;
    }

    /**
     * @param string $tableName
     *
     * @return string
     * @throws \Exception
     * @deprecated use getPrimaryKey instead
     */
    public function getPrimaryColumn($tableName)
    {
        $primaryKey = $this->getPrimaryKey($tableName);
        if (empty($primaryKey)) {
            return null;
        } elseif (count($primaryKey) > 1) {
            throw new \Exception(
                'getPrimaryColumn method does not support composite primary keys, use getPrimaryKey instead'
            );
        }

        return $primaryKey[0];
    }

    /**
     * @param string $tableName
     *
     * @return array[string]
     */
    public function getPrimaryKey($tableName)
    {
        return [];
    }

    /**
     * @return bool
     */
    protected function flushPrimaryColumnCache()
    {
        $this->primaryKeys = [];

        return empty($this->primaryKeys);
    }

    public function update($table, array $data, array $criteria)
    {
        if (empty($data)) {
            throw new \InvalidArgumentException(
                "Query update can't be prepared without data."
            );
        }

        $set = [];
        foreach ($data as $column => $value) {
            $set[] = $this->getQuotedName($column) . " = ?";
        }

        $where = $this->generateWhereClause($criteria);

        return sprintf('UPDATE %s SET %s %s', $this->getQuotedName($table), implode(', ', $set), $where);
    }

    public function getOptions()
    {
        return $this->options;
    }
}
