<?php
namespace Codeception\Module;

class Db extends \Codeception\Module {

    protected $sql;
    protected $dbh;

    protected $requiredFields = array('dsn', 'user', 'password');

    public function _initialize() {

        if (!file_exists($this->config['dump'])) {
            throw new \Codeception\Exception\ModuleConfig(__CLASS__, "
                File with dump deesn't exist.\n
                Please, check path for sql file: ".$this->config['dump']);
        }

        // not necessary to specify dump
        if (isset($this->config['dump'])) {
            $sql = file_get_contents($this->config['dump']);
            $this->sql = $sql;

            try {
                $dbh = new \PDO($this->config['dsn'], $this->config['user'], $this->config['password']);
                $this->dbh = $dbh;
            } catch (\PDOException $e) {
                throw new \Codeception\Exception\Module(__CLASS__, $e->getMessage());
            }
        }
    }

    public function _before() {

        $dbh = $this->dbh;
        if (!$dbh) {
            throw new \Codeception\Exception\ModuleConfig(__CLASS__, "No connection to database. Remove this module from config if you don't need database repopulation");
        }
        try {
            $res = $dbh->query('show tables')->fetchAll();
            foreach ($res as $row) {
                $dbh->exec('drop table '.$row[0]);
            }
            $this->queries = explode(';', $this->sql);

            foreach ($this->queries as $query) {
                $dbh->exec($query);
            }

        } catch (\PDOException $e) {
            throw new \Codeception\Exception\Module(__CLASS__, $e->getMessage());
        }
    }
    
    public function seeInDatabase($table, $criteria) {

    }
    
    
    public function dontSeeInDatabase($table, $criteria) {
        
    }


}
