<?php
namespace Codeception\Lib\Driver;

class Oci extends Db
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

    /**
     * @param string $tableName
     *
     * @return array[string]
     */
    public function getPrimaryKey($tableName)
    {
        if (!isset($this->primaryKeys[$tableName])) {
            $primaryKey = [];
            $query = "SELECT cols.column_name
                FROM all_constraints cons, all_cons_columns cols
                WHERE cols.table_name = ?
                AND cons.constraint_type = 'P'
                AND cons.constraint_name = cols.constraint_name
                AND cons.owner = cols.owner
                ORDER BY cols.table_name, cols.position";
            $stmt = $this->executeQuery($query, [$tableName]);
            $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($columns as $column) {
                $primaryKey []= $column['column_name'];
            }
            $this->primaryKeys[$tableName] = $primaryKey;
        }

        return $this->primaryKeys[$tableName];
    }
}
