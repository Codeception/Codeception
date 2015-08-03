<?php
namespace Codeception\Lib\Driver;

class Oci extends Oracle
{
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
        $query = '';
        $delimiter = '//';
        $delimiterLength = 2;

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
}
