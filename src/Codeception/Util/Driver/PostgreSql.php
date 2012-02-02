<?php
namespace Codeception\Util\Driver;

class PostgreSql extends Db
{
    protected $putline = false;

    protected $connection = null;

    public function cleanup()
    {
        $this->dbh->exec("GRANT ALL ON DATABASE codeception_test TO ".$this->user);

        $drops = $this->dbh->query("select 'drop table if exists ' || tablename || ' cascade;' from pg_tables where schemaname = 'public' ;")->fetchAll();

        if (!$drops) return;
        foreach ($drops as $drop) {
            $this->dbh->exec($drop[0]);
        }
    }

    public function sqlLine($sql)
    {
        if (!$this->putline) return parent::sqlLine($sql);

        if ($sql == '\.') {
            $this->putline = false;
            pg_put_line($this->connection, $sql."\n");
            pg_end_copy($this->connection);
        } else {
            pg_put_line($this->connection, $sql."\n");
        }
        return true;
    }

    public function sqlQuery($query)
    {
        $constring = str_replace(';',' ',substr($this->dsn,6));
        $constring .= ' user='.$this->user;
        $constring .= ' password='.$this->password;
        $this->connection = pg_connect($constring);
        pg_query($this->connection, $query);
        if (strpos(trim($query), 'COPY ') === 0) $this->putline = true;
    }
}
