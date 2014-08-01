<?php
namespace Codeception\Lib\Driver;

class Oci extends Oracle
{
    public function select($column, $table, array &$criteria) {
        $where = $criteria ? "where %s" : '';
        $query = "select %s from %s $where";
        $params = array();
        foreach ($criteria as $k => $v) {
            if ($v === null) {
                $params[] = "$k IS NULL ";
                unset($criteria[$k]);
            } else {
                $params[] = "$k = ? ";
            }
        }
        $params = implode('AND ', $params);

        return sprintf($query, $column, $table, $params);
    }

    /**
     * SQL commands should ends with `//` in the dump file
     * IF you want to load triggers too.
     * IF you do not want to load triggers you can use the `;` characters
     * but in this case you need to change the $delimiter from `//` to `;`
     *
     * @param $sql
     */
    public function load($sql)
    {
        $query           = '';
        $delimiter       = '//';
        $delimiterLength = 2;

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

            $query .= "\n" . rtrim($sqlLine);

            if (substr($query, - 1 * $delimiterLength, $delimiterLength) == $delimiter) {
                $this->sqlToRun = substr($query, 0, - 1 * $delimiterLength);
                $this->sqlQuery($this->sqlToRun);
                $query = "";
            }
        }
    }
}
