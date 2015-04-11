<?php
namespace Codeception\Lib\Driver;

class PostgreSql extends Db
{
    protected $putline = false;

    protected $connection = null;

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

            if (strpos(trim($sqlLine), '$$') === 0) {
                $dollarsOpen = !$dollarsOpen;
            }

            $query .= "\n" . rtrim($sqlLine);

            if (!$dollarsOpen && substr($query, -1 * $delimiterLength, $delimiterLength) == $delimiter) {
                $this->sqlToRun = substr($query, 0, -1 * $delimiterLength);
                $this->sqlQuery($this->sqlToRun);
                $query = "";
            }
        }
    }

    public function cleanup()
    {
        $tables = $this->dbh
            ->query("SELECT 'DROP TABLE IF EXISTS \"' || tablename || '\" cascade;' FROM pg_tables WHERE schemaname = 'public';")
            ->fetchAll();

        $sequences = $this->dbh
            ->query("SELECT 'DROP SEQUENCE IF EXISTS \"' || relname || '\" cascade;' FROM pg_class WHERE relkind = 'S';")
            ->fetchAll();

        $types = $this->dbh
            ->query("SELECT 'DROP TYPE IF EXISTS \"' || pg_type.typname || '\" cascade;' FROM pg_type JOIN pg_enum ON pg_enum.enumtypid = pg_type.oid GROUP BY pg_type.typname;")
            ->fetchAll();

        $drops = array_merge($tables, $sequences, $types);
        if ($drops) {
            foreach ($drops as $drop) {
                $this->dbh->exec($drop[0]);
            }
        }
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
            if (!extension_loaded(
                'pgsql'
            )
            ) {
                throw new \Codeception\Exception\ModuleException('\Codeception\Module\Db', "To run 'COPY' commands 'pgsql' extension should be installed");
            }
            $constring = str_replace(';', ' ', substr($this->dsn, 6));
            $constring .= ' user=' . $this->user;
            $constring .= ' password=' . $this->password;
            $this->connection = pg_connect($constring);
            pg_query($this->connection, $query);
            $this->putline = true;
        } else {
            $this->dbh->exec($query);
        }
    }

    public function select($column, $table, array &$criteria)
    {
        $where = $criteria ? "where %s" : '';
        $query = 'select %s from "%s" ' . $where;
        $params = [];
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

    public function lastInsertId($table)
    {
        /*We make an assumption that the sequence name for this table is based on how postgres names sequences for SERIAL columns */
        $sequenceName = $table . '_id_seq';
        return $this->getDbh()->lastInsertId($sequenceName);
    }

    public function getQuotedName($name)
    {
        $name = explode('.', $name);
        $name = array_map(
            function ($data) {
                return '"' . $data . '"';
            }, $name
        );
        return implode('.', $name);
    }
}
