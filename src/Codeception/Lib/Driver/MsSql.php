<?php
namespace Codeception\Lib\Driver;

class MsSql extends Db
{
    public function cleanup()
    {
        $this->dbh->exec("DECLARE tables_cursor CURSOR FOR SELECT name FROM sysobjects WHERE type = 'U'");
        $this->dbh->exec("OPEN tables_cursor DECLARE @tablename sysname");
        $this->dbh->exec(
                  "FETCH NEXT FROM tables_cursor INTO @tablename
                                               WHILE (@@FETCH_STATUS <> -1)
                                               BEGIN
                                               EXEC ('DROP TABLE ' + @tablename)
                                               FETCH NEXT FROM tables_cursor INTO @tablename
                                               END
                          "
        );
        $this->dbh->exec('DEALLOCATE tables_cursor');
    }
}
