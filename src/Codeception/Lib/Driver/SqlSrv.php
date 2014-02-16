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
    
    public function select($column, $table, array $criteria)
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
    
    public function deleteQuery($table, $id)
    {
        $query = "delete from " 
            . $this->getQuotedName($table)
            . " where " . $this->getQuotedName('id') . " = $id";
        
        $this->sqlQuery($query);
    }
}