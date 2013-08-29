<?php
namespace Codeception\Util\Driver;

class PostgreSql extends Db
{
    protected $putline = false;

    protected $connection = null;

    public function cleanup()
    {
        $tables = $this->dbh->query("SELECT 'DROP TABLE IF EXISTS \"' || tablename || '\" cascade;' FROM pg_tables WHERE schemaname = 'public';")->fetchAll();
        $sequences = $this->dbh->query("SELECT 'DROP SEQUENCE IF EXISTS \"' || relname || '\" cascade;' FROM pg_class WHERE relkind = 'S';")->fetchAll();

        $drops = array_merge($tables, $sequences);
        if ($drops) {
            foreach ($drops as $drop) {
                $this->dbh->exec($drop[0]);
            }
        }
    }

    public function sqlLine($sql)
    {
        if (!$this->putline) return parent::sqlLine($sql);

        if ($sql == '\.') {
            $this->putline = false;
            pg_put_line($this->connection, $sql."\n");
            pg_end_copy($this->connection);
            pg_close($this->connection);
        } else {
            pg_put_line($this->connection, $sql."\n");
        }
        return true;
    }

    public function sqlQuery($query)
    {
        if (strpos(trim($query), 'COPY ') === 0) {
            if (!extension_loaded('pgsql')) throw new \Codeception\Exception\Module('\Codeception\Module\Db', "To run 'COPY' commands 'pgsql' extension should be installed");
            $constring = str_replace(';',' ',substr($this->dsn,6));
            $constring .= ' user='.$this->user;
            $constring .= ' password='.$this->password;
            $this->connection = pg_connect($constring);
            pg_query($this->connection, $query);
            $this->putline = true;
        } else {
            $this->dbh->exec($query);
        }
    }

    public function select($column, $table, array &$criteria) {
        $query = 'select %s from "%s" where %s';
        $params = array();
        foreach ($criteria as $k => $v) {
            if($v === NULL) {
                $params[] = "$k IS NULL ";
                unset($criteria[$k]);
            } else {
                $params[] = "$k = ? ";
            }
        }
        $params = implode('AND ', $params);

        return sprintf($query, $column, $table, $params);
    }
}
