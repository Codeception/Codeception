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

    public function cleanup()
    {
    }

    public function load($sql)
    {
        $query = "";
        foreach ($sql as $sql_line) {

            $parsed = $this->sqlLine($sql_line);
            if ($parsed) continue;

            $query .= $sql_line;

            if (substr(rtrim($query), -1, 1) == ';') {
                $this->sqlQuery($query);
                $query = "";
            }
        }
    }

    protected function sqlLine($sql)
    {
        if (trim($sql) == "") return true;
        if (trim($sql) == ";") return true;
        if (preg_match('~^--.*?~s', $sql)) return true;
        return false;
    }

    protected function sqlQuery($query)
    {
        $this->dbh->exec($query);
    }


}
