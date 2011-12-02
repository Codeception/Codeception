<?php
namespace Codeception\Module;

class Db extends \Codeception\Module
{

    protected $sql;
    protected $dbh;

    protected $requiredFields = array('dsn', 'user', 'password');

    public function _initialize()
    {

        if (!file_exists($this->config['dump'])) {
            throw new \Codeception\Exception\ModuleConfig(__CLASS__, "
                File with dump deesn't exist.\n
                Please, check path for sql file: " . $this->config['dump']);
        }

        // not necessary to specify dump
        if (isset($this->config['dump'])) {
            $sql = file_get_contents($this->config['dump']);
//            $sql = preg_replace("%(#(//)).*%","",$sql);
            $sql = preg_replace('%/\*(?:(?!\*/).)*\*/%s',"",$sql);
            $this->sql = explode("\r\n", $sql);

            try {
                $dbh = new \PDO($this->config['dsn'], $this->config['user'], $this->config['password']);
                $this->dbh = $dbh;
            } catch (\PDOException $e) {
                throw new \Codeception\Exception\Module(__CLASS__, $e->getMessage());
            }
        }
    }

    public function _before()
    {
        $dbh = $this->dbh;
        if (!$dbh) {
            throw new \Codeception\Exception\ModuleConfig(__CLASS__, "No connection to database. Remove this module from config if you don't need database repopulation");
        }
        try {

            $this->dbh->exec('SET FOREIGN_KEY_CHECKS=0');

            $res = $dbh->query('show tables')->fetchAll();
            foreach ($res as $row) {
                $dbh->exec('drop table ' . $row[0]);
            }

            $query = "";
            foreach ($this->sql as $sql_line) {
                if (trim($sql_line) != "" && trim($sql_line) != ";") {
                    $query .= $sql_line;
                    if (substr(rtrim($query), -1,1) == ';') {
                        $this->dbh->exec($query);
                        $query = "";
                    }
                }
            }

            $this->dbh->exec('SET FOREIGN_KEY_CHECKS=1');

        } catch (\Exception $e) {
            throw new \Codeception\Exception\Module(__CLASS__, $e->getMessage());
        }
    }

    public function seeInDatabase($table, $criteria = array())
    {
        $query = "select count(*) from `%s` where %s";

        $params = array();
        foreach ($criteria as $k => $v) {
            $params[] = "`$k` = ?";
        }
        $params = implode('AND ',$params);

        $query = sprintf($query, $table, $params);
        $sth = $this->dbh->prepare($query);
        $sth->execute(array_values($criteria));
        return $sth->fetchColumn();
    }

    protected function proceedSeeInDatabase($table, $criteria)


    public function dontSeeInDatabase($table, $criteria)
    {

    }


}
