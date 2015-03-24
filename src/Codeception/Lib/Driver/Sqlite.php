<?php
namespace Codeception\Lib\Driver;

class Sqlite extends Db
{

    protected $hasSnapshot = false;
    protected $filename = '';
    protected $con = null;

    public function __construct($dsn, $user, $password)
    {
        parent::__construct($dsn, $user, $password);
        $this->filename = \Codeception\Configuration::projectDir() . substr($this->dsn, 7);
        $this->dsn = 'sqlite:' . $this->filename;
    }

    public function cleanup()
    {
        $this->dbh = null;
        file_put_contents($this->filename, '');
        $this->dbh = self::connect($this->dsn, $this->user, $this->password);
    }

    public function load($sql)
    {
        if ($this->hasSnapshot) {
            $this->dbh = null;
            file_put_contents($this->filename, file_get_contents($this->filename . '_snapshot'));
            $this->dbh = new \PDO($this->dsn, $this->user, $this->password);
        } else {
            if (file_exists($this->filename . '_snapshot')) {
                unlink($this->filename . '_snapshot');
            }
            parent::load($sql);
            copy($this->filename, $this->filename . '_snapshot');
            $this->hasSnapshot = true;
        }
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
