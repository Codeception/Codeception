<?php
namespace Codeception\Lib\Driver;

class Oracle extends Db
{
    public function cleanup()
    {
        $this->dbh->exec(
            "BEGIN
                            FOR i IN (SELECT trigger_name FROM user_triggers)
                              LOOP
                                EXECUTE IMMEDIATE('DROP TRIGGER ' || user || '.' || i.trigger_name);
                              END LOOP;
                          END;"
        );
        $this->dbh->exec(
            "BEGIN
                            FOR i IN (SELECT table_name FROM user_tables)
                              LOOP
                                EXECUTE IMMEDIATE('DROP TABLE ' || user || '.' || i.table_name || ' CASCADE CONSTRAINTS');
                              END LOOP;
                          END;"
        );
        $this->dbh->exec(
            "BEGIN
                            FOR i IN (SELECT sequence_name FROM user_sequences)
                              LOOP
                                EXECUTE IMMEDIATE('DROP SEQUENCE ' || user || '.' || i.sequence_name);
                              END LOOP;
                          END;"
        );
    }
}
