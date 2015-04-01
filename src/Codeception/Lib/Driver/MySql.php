<?php
namespace Codeception\Lib\Driver;

class MySql extends Db
{
    public function cleanup($ignoreDrop=false)
    {
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=0;');
        $res = $this->dbh->query("SHOW FULL TABLES WHERE TABLE_TYPE LIKE '%TABLE';")->fetchAll();
        foreach ($res as $row) {
            try {
                $this->dbh->exec('drop table `' . $row[0] . '`');
            } catch (\PDOException $e) {
                if ($ignoreDrop) {
                    trigger_error('Unable to drop table '.$row[0].' during cleanup', E_WARNING);
                } else {
                    throw $e;
                }
            }
        }
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    protected function sqlQuery($query)
    {
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=0;');
        parent::sqlQuery($query);
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function select($column, $table, array &$criteria) {
        $where = $criteria ? "where %s" : '';
        $table = $this->getQuotedName($table);
        $query = "select %s from %s $where";
        $params = array();
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

    public function getQuotedName($name)
    {
        return '`' . str_replace('.', '`.`', $name) . '`';
    }
}
