<?php
namespace Codeception\Lib\Driver;

class SqlSrv extends Db
{
    public function getDb()
    {
        $matches = array();
        $matched = preg_match('~Database=(.*);~s', $this->dsn, $matches);
        
        if (!$matched)
            return false;
        
        return $matches[1];
    }
    
    public function cleanup()
    {
        $this->dbh->exec("
            DECLARE constraints_cursor CURSOR FOR SELECT name, parent_object_id FROM sys.foreign_keys;
            OPEN constraints_cursor
            DECLARE @constraint sysname;
            DECLARE @parent int;
            DECLARE @table nvarchar(128);
            FETCH NEXT FROM constraints_cursor INTO @constraint, @parent;
            WHILE (@@FETCH_STATUS <> -1)
            BEGIN
                SET @table = OBJECT_NAME(@parent)
                EXEC ('ALTER TABLE [' + @table + '] DROP CONSTRAINT [' + @constraint + ']')
                FETCH NEXT FROM constraints_cursor INTO @constraint, @parent;
            END
            DEALLOCATE constraints_cursor;");

        $this->dbh->exec("
            DECLARE tables_cursor CURSOR FOR SELECT name FROM sysobjects WHERE type = 'U';
            OPEN tables_cursor DECLARE @tablename sysname;
            FETCH NEXT FROM tables_cursor INTO @tablename;
            WHILE (@@FETCH_STATUS <> -1)
            BEGIN
                EXEC ('DROP TABLE [' + @tablename + ']')
                FETCH NEXT FROM tables_cursor INTO @tablename;
            END
            DEALLOCATE tables_cursor;");
    }
    
    public function select($column, $table, array &$criteria)
    {
        $where  = $criteria ? "where %s" : '';
        $query  = "select %s from " . $this->getQuotedName('%s') . " $where";
        $params = array();
        
        foreach ($criteria as $k => $v) {
            $params[] = $this->getQuotedName($k) . " = ? ";
        }
        
        $params = implode('AND ', $params);
        
        return sprintf($query, $column, $table, $params);
    }
    
    public function getQuotedName($name)
    {
        return '[' . $name . ']';
    }
    
    public function deleteQuery($table, $id, $primaryKey = 'id')
    {
        $query = "delete from " 
            . $this->getQuotedName($table)
            . " where " . $this->getQuotedName($primaryKey) . " = $id";
        
        $this->sqlQuery($query);
    }
    
    /**
     * Get a primary column name of a table.
     *
     * @param string $tableName
     * @throws \Exception
     * @return string of a primary column name.
     */
    public function getPrimaryColumn($tableName)
    {
    	if (false === isset($this->primaryColumns[$tableName])) {
    		$stmt = $this->getDbh()->query("
SELECT Col.Column_Name from
	INFORMATION_SCHEMA.TABLE_CONSTRAINTS Tab,
	INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE Col
WHERE
	Col.Constraint_Name = Tab.Constraint_Name
    AND Col.Table_Name = Tab.Table_Name
    AND Constraint_Type = 'PRIMARY KEY' AND Col.Table_Name = '" . $tableName . "'");
    		$columnInformation = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    		if (true === empty($columnInformation)) { // Need a primary key
    			throw new \Exception('Table ' . $tableName . ' is not valid or doesn\'t have no primary key');
    		}
    
    		$this->primaryColumns[$tableName] = $columnInformation['Column_Name'];
    	}
    
    	return $this->primaryColumns[$tableName];
    }
}
