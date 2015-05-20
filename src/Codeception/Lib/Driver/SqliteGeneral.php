<?php
namespace Codeception\Lib\Driver;

class SqliteGeneral extends Db
{

    protected $hasSnapshot = false;
    protected $filename = '';
    protected $con = null;

    public function __construct($dsn, $user, $password)
    {
        parent::__construct($dsn, $user, $password);
        $this->filename = \Codeception\Configuration::projectDir() . substr($this->dsn, 7);
        $this->dsn      = 'sqlite:' . $this->filename;
    }

    public function cleanup()
    {
        $this->dbh = null;
        file_put_contents($this->filename, '');
        $this->dbh = self::connect($this->dsn, $this->user, $this->password);
    }

    public function load($sql)
    {
        $this->dbh->exec('PRAGMA writable_schema = 1;');
        $this->dbh->exec('PRAGMA ignore_check_constraints = 1;');
        parent::load($sql);
        $this->dbh->exec('PRAGMA ignore_check_constraints = 0;');
        $this->dbh->exec('PRAGMA writable_schema = 0;');
    }

    /**
     * @param string $tableName
     *
     * @return string
     */
    public function getPrimaryColumn($tableName)
    {
        // @TODO: Implement this for SQLite later
        return 'id';
    }
}
