<?php

namespace Codeception\Lib\Driver;

use Codeception\Exception\ModuleException;

class PostgreSql extends Db
{
    protected $putline = false;

    protected $connection = null;

    protected $searchPath = null;

    /**
     * Loads a SQL file.
     *
     * @param string $sql sql file
     */
    public function load($sql)
    {
        $query = '';
        $delimiter = ';';
        $delimiterLength = 1;

        $dollarsOpen = false;
        foreach ($sql as $sqlLine) {
            if (preg_match('/DELIMITER ([\;\$\|\\\\]+)/i', $sqlLine, $match)) {
                $delimiter = $match[1];
                $delimiterLength = strlen($delimiter);
                continue;
            }

            $parsed = trim($query) == '' && $this->sqlLine($sqlLine);
            if ($parsed) {
                continue;
            }

            // Ignore $$ inside SQL standard string syntax such as in INSERT statements.
            if (!preg_match('/\'.*\$\$.*\'/', $sqlLine)) {
                $pos = strpos($sqlLine, '$$');
                if (($pos !== false) && ($pos >= 0)) {
                    $dollarsOpen = !$dollarsOpen;
                }
            }

            if (preg_match('/SET search_path = .*/i', $sqlLine, $match)) {
                $this->searchPath = $match[0];
            }

            $query .= "\n" . rtrim($sqlLine);

            if (!$dollarsOpen && substr($query, -1 * $delimiterLength, $delimiterLength) == $delimiter) {
                $this->sqlQuery(substr($query, 0, -1 * $delimiterLength));
                $query = '';
            }
        }

        if ($query !== '') {
            $this->sqlQuery($query);
        }
    }

    public function cleanup()
    {
        $this->dbh->exec('DROP SCHEMA IF EXISTS public CASCADE;');
        $this->dbh->exec('CREATE SCHEMA public;');
    }

    public function sqlLine($sql)
    {
        if (!$this->putline) {
            return parent::sqlLine($sql);
        }

        if ($sql == '\.') {
            $this->putline = false;
            pg_put_line($this->connection, $sql . "\n");
            pg_end_copy($this->connection);
            pg_close($this->connection);
        } else {
            pg_put_line($this->connection, $sql . "\n");
        }

        return true;
    }

    public function sqlQuery($query)
    {
        if (strpos(trim($query), 'COPY ') === 0) {
            if (!extension_loaded('pgsql')) {
                throw new ModuleException(
                    '\Codeception\Module\Db',
                    "To run 'COPY' commands 'pgsql' extension should be installed"
                );
            }
            if (defined('HHVM_VERSION')) {
                throw new ModuleException(
                    '\Codeception\Module\Db',
                    "'COPY' command is not supported on HHVM, please use INSERT instead"
                );
            }
            $constring = str_replace(';', ' ', substr($this->dsn, 6));
            $constring .= ' user=' . $this->user;
            $constring .= ' password=' . $this->password;
            $this->connection = pg_connect($constring);

            if ($this->searchPath !== null) {
                pg_query($this->connection, $this->searchPath);
            }

            pg_query($this->connection, $query);
            $this->putline = true;
        } else {
            $this->dbh->exec($query);
        }
    }

    /**
     * Get the last inserted ID of table.
     */
    public function lastInsertId($table)
    {
        /*
         * We make an assumption that the sequence name for this table
         * is based on how postgres names sequences for SERIAL columns
         */

        $sequenceName = $this->getQuotedName($table . '_id_seq');
        $lastSequence = null;

        try {
            $lastSequence = $this->getDbh()->lastInsertId($sequenceName);
        } catch (\PDOException $e) {
            // in this case, the sequence name might be combined with the primary key name
        }

        // here we check if for instance, it's something like table_primary_key_seq instead of table_id_seq
        // this could occur when you use some kind of import tool like pgloader
        if (!$lastSequence) {
            $primaryKeys = $this->getPrimaryKey($table);
            $pkName = array_shift($primaryKeys);
            $lastSequence = $this->getDbh()->lastInsertId($this->getQuotedName($table . '_' . $pkName . '_seq'));
        }

        return $lastSequence;
    }

    /**
     * Returns the primary key(s) of the table, based on:
     * https://wiki.postgresql.org/wiki/Retrieve_primary_key_columns.
     *
     * @param string $tableName
     *
     * @return array[string]
     */
    public function getPrimaryKey($tableName)
    {
        if (!isset($this->primaryKeys[$tableName])) {
            $primaryKey = [];
            $query = "SELECT a.attname
                FROM   pg_index i
                JOIN   pg_attribute a ON a.attrelid = i.indrelid
                                     AND a.attnum = ANY(i.indkey)
                WHERE  i.indrelid = '$tableName'::regclass
                AND    i.indisprimary";
            $stmt = $this->executeQuery($query, []);
            $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($columns as $column) {
                $primaryKey []= $column['attname'];
            }
            $this->primaryKeys[$tableName] = $primaryKey;
        }

        return $this->primaryKeys[$tableName];
    }
}
