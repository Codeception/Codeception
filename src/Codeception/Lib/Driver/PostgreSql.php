<?php
namespace Codeception\Lib\Driver;

use Codeception\Exception\ModuleException;

class PostgreSql extends Db
{
    protected $putline = false;

    protected $connection = null;

    protected $searchPath = null;

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
                $this->sqlToRun = substr($query, 0, -1 * $delimiterLength);
                $this->sqlQuery($this->sqlToRun);
                $query = "";
            }
        }
    }

    public function cleanup()
    {
        $tables = $this->dbh
            ->query(
                "SELECT 'DROP TABLE IF EXISTS \"' || tablename || '\" cascade;' "
                . "FROM pg_tables WHERE schemaname = 'public';"
            )
            ->fetchAll();

        $sequences = $this->dbh
            ->query(
                "SELECT 'DROP SEQUENCE IF EXISTS \"' || relname || '\" cascade;' FROM pg_class WHERE relkind = 'S';"
            )
            ->fetchAll();

        // @codingStandardsIgnoreStart
        $types = $this->dbh
            ->query("SELECT 'DROP TYPE IF EXISTS \"' || t.typname || '\" cascade;' FROM pg_catalog.pg_type t LEFT JOIN pg_catalog.pg_namespace n ON n.oid = t.typnamespace WHERE (t.typrelid = 0 OR (SELECT c.relkind = 'c' FROM pg_catalog.pg_class c WHERE c.oid = t.typrelid)) AND NOT EXISTS(SELECT 1 FROM pg_catalog.pg_type el WHERE el.oid = t.typelem AND el.typarray = t.oid) AND n.nspname <> 'pg_catalog' AND n.nspname <> 'information_schema' AND pg_catalog.pg_type_is_visible(t.oid);")
            ->fetchAll();
        // @codingStandardsIgnoreEnd

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
            if (!extension_loaded('pgsql')) {
                throw new ModuleException(
                    '\Codeception\Module\Db',
                    "To run 'COPY' commands 'pgsql' extension should be installed"
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

    public function lastInsertId($table)
    {
        /**
         * We make an assumption that the sequence name for this table
         * is based on how postgres names sequences for SERIAL columns
         */
        $sequenceName = $this->getQuotedName($table . '_id_seq');
        return $this->getDbh()->lastInsertId($sequenceName);
    }

    public function getQuotedName($name)
    {
        $name = explode('.', $name);
        $name = array_map(
            function ($data) {
                return '"' . $data . '"';
            },
            $name
        );
        return implode('.', $name);
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
            $query = 'SELECT a.attname
                FROM   pg_index i
                JOIN   pg_attribute a ON a.attrelid = i.indrelid
                                     AND a.attnum = ANY(i.indkey)
                WHERE  i.indrelid = ?::regclass
                AND    i.indisprimary';
            $stmt = $this->executeQuery($query, [$tableName]);
            $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($columns as $column) {
                $primaryKey []= $column['attname'];
            }
            $this->primaryKeys[$tableName] = $primaryKey;
        }

        return $this->primaryKeys[$tableName];
    }
}
