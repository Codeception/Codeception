<?php
namespace Codeception\Util\Driver;

class Oci extends Oracle
{
    public function select($column, $table, array &$criteria) {
        $where = $criteria ? "where %s" : '';
        $query = "select %s from %s $where";
        $params = array();
        foreach ($criteria as $k => $v) {
            $params[] = "$k = ? ";
        }
        $params = implode('AND ', $params);

        return sprintf($query, $column, $table, $params);
    }
}
