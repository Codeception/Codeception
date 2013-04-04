<?php
namespace Codeception\Util\Driver;

class Db
{
    protected $dbh;
    protected $dsn;

    public static function connect($dsn, $user, $password)
    {
        $dbh = new \PDO($dsn, $user, $password);
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $dbh;
    }

    /**
     * @static
     * @param $dsn
     * @param $user
     * @param $password
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
    
    public function getDbh() {
        return $this->dbh;
    }
    
    public function getDb()
    {
        $matches = array();
        $matched = preg_match('~dbname=(.*);~s', $this->dsn, $matches);
        if (!$matched) return false;
        return $matches[1];
    }        

    public function cleanup()
    {
    }

    public function load($sql)
    {
        $query           = '';
        $delimiter       = ';';
        $delimiterLength = 1;

        foreach ($sql as $sqlLine) {
            if (preg_match('/DELIMITER ([\;\$\|\\\\]+)/i', $sqlLine, $match)) {
                $delimiter       = $match[1];
                $delimiterLength = strlen($delimiter);
                continue;
            }

            $parsed = $this->sqlLine($sqlLine);
            if ($parsed) {
                continue;
            }

            $query .= rtrim($sqlLine);

            if (substr($query, - 1 * $delimiterLength, $delimiterLength) == $delimiter) {
                $this->sqlQuery(substr($query, 0, - 1 * $delimiterLength));
                $query = "";
            }
        }
    }

    public function insert($table, array $data)
    {
        $query = "insert into %s (%s) values (%s)";
        return sprintf($query, $table, implode(', ', array_keys($data)), implode(', ', array_fill(0, count($data),'?')));
    }

    public function select($column, $table, array $criteria) {
        $query = "select %s from %s where %s";
        $params = array();
        foreach ($criteria as $k => $v) {
            $params[] = "$k = ? ";
        }
        $params = implode('AND ', $params);

        return sprintf($query, $column, $table, $params);
    }

    public function deleteQuery($table, $id)
    {
        $query = "delete from $table where id = $id";
        $this->sqlQuery($query);
    }

    protected function sqlLine($sql)
    {
        if (trim($sql) == "") return true;
        if (trim($sql) == ";") return true;
        if (preg_match('~^(--.*?)|(#)~s', $sql)) return true;
        return false;
    }

    protected function sqlQuery($query)
    {
        $this->dbh->exec($query);
    }


}
