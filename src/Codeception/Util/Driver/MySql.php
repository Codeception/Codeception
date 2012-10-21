<?php
namespace Codeception\Util\Driver;

class MySql extends Db
{
    public function cleanup() {
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=0;');
        $res = $this->dbh->query('show tables')->fetchAll();
        foreach ($res as $row) {
            $this->dbh->exec('drop table `' . $row[0] . '`');
        }
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    protected function sqlQuery($query)
    {
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=0;');
        parent::sqlQuery($query);
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function select($column, $table, array $criteria) {
        $query = "select %s from `%s` where %s";
        $params = array();
        foreach ($criteria as $k => $v) {
            $params[] = "`$k` = ? ";
        }
        $params = implode('AND ', $params);

        return sprintf($query, $column, $table, $params);
    }
}
