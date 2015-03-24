<?php
namespace Codeception\Lib\Driver;

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

    /**
     * @var string
     */
    public $sqlToRun;

    /**
     * associative array with table name => primary-key
     *
     * @var array
     */
    protected $primaryColumns = [];

    public static function connect($dsn, $user, $password)
    {
        $dbh = new \PDO($dsn, $user, $password);
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $dbh;
    }

    /**
     * @static
     *
     * @param $dsn
     * @param $user
     * @param $password
     *
     * @return Db|MsSql|MySql|Oracle|PostgreSql|Sqlite
     */
    public static function create($dsn, $user, $password)
    {
        $provider = self::getProvider($dsn);

        switch ($provider) {
            case 'sqlite':
                return new Sqlite($dsn, $user, $password);
            case 'mysql':
                return new MySql($dsn, $user, $password);
            case 'pgsql':
                return new PostgreSql($dsn, $user, $password);
            case 'mssql':
                return new MsSql($dsn, $user, $password);
            case 'oracle':
                return new Oracle($dsn, $user, $password);
            case 'sqlsrv':
                return new SqlSrv($dsn, $user, $password);
            case 'oci':
                return new Oci($dsn, $user, $password);
            default:
                return new Db($dsn, $user, $password);
        }
    }

    public static function getProvider($dsn)
    {
        return substr($dsn, 0, strpos($dsn, ':'));
    }

    public function __construct($dsn, $user, $password)
    {
        $this->dbh = new \PDO($dsn, $user, $password);
        $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->dsn = $dsn;
        $this->user = $user;
        $this->password = $password;
    }

    public function getDbh()
    {
        return $this->dbh;
    }

    public function getDb()
    {
        $matches = [];
        $matched = preg_match('~dbname=(.*);~s', $this->dsn, $matches);
        if (!$matched) {
            return false;
        }

        return $matches[1];
    }

    public function cleanup()
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
                $this->sqlToRun = substr($query, 0, -1 * $delimiterLength);
                $this->sqlQuery($this->sqlToRun);
                $query = "";
            }
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
        $where  = $criteria ? "where %s" : '';
        $query  = "select %s from `%s` $where";
        $params = [];
        foreach ($criteria as $k => $v) {
            $k = $this->getQuotedName($k);
            if ($v === null) {
                $params[] = "$k IS ?";
            } else {
                $params[] = "$k = ?";
            }
        }
        $params = implode(' AND ', $params);

        return sprintf($query, $column, $table, $params);
    }

    public function deleteQuery($table, $id, $primaryKey = 'id')
    {
        $query = 'DELETE FROM ' . $table . ' WHERE ' . $primaryKey . ' = ' . $id;
        $this->sqlQuery($query);
    }

    public function lastInsertId($table)
    {
        return $this->getDbh()->lastInsertId();
    }

    public function getQuotedName($name)
    {
        return $name;
    }

    protected function sqlLine($sql)
    {
        if (trim($sql) == "") {
            return true;
        }
        if (trim($sql) == ";") {
            return true;
        }
        if (preg_match('~^((--.*?)|(#))~s', $sql)) {
            return true;
        }

        return false;
    }

    protected function sqlQuery($query)
    {
        $this->dbh->exec($query);
    }

    /**
     * @param string $tableName
     *
     * @return string
     * @throws \Exception
     */
    public function getPrimaryColumn($tableName)
    {
        if (false === isset($this->primaryColumns[$tableName])) {
            $stmt = $this->getDbh()->query('SHOW KEYS FROM `' . $tableName . '` WHERE Key_name = "PRIMARY"');
            $columnInformation = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (true === empty($columnInformation)) { // Need a primary key
                throw new \Exception('Table ' . $tableName . ' is not valid or doesn\'t have no primary key');
            }

            $this->primaryColumns[$tableName] = $columnInformation['Column_name'];
        }

        return $this->primaryColumns[$tableName];
    }

    /**
     * @return bool
     */
    protected function flushPrimaryColumnCache()
    {
        $this->primaryColumns = [];

        return empty($this->primaryColumns);
    }
}
