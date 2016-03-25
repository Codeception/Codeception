<?php
namespace Codeception\Lib\Driver;

use Codeception\Configuration;

class Sqlite extends Db
{
    protected $hasSnapshot = false;
    protected $filename = '';
    protected $con = null;

    public function __construct($dsn, $user, $password)
    {
        $this->filename = Configuration::projectDir() . substr($dsn, 7);
        $this->dsn = 'sqlite:' . $this->filename;
        parent::__construct($this->dsn, $user, $password);
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
     * @return array[string]
     */
    public function getPrimaryKey($tableName)
    {
        if (!isset($this->primaryKeys[$tableName])) {
            $primaryKey = [];
            $query = 'PRAGMA table_info(' . $this->getQuotedName($tableName) . ')';
            $stmt = $this->executeQuery($query, []);
            $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($columns as $column) {
                if ($column['pk'] !== '0') {
                    $primaryKey []= $column['name'];
                }
            }

            $this->primaryKeys[$tableName] = $primaryKey;
        }

        return $this->primaryKeys[$tableName];
    }
}
