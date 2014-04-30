<?php

namespace Codeception\Lib\Driver;

class MongoDb
{
    private $dbh;
    private $dsn;
    private $dbName;
    private $host;
    private $user;
    private $password;

    public static function connect($dsn, $user, $password)
    {
        throw new \Exception(__CLASS__ . '::connect() - hm, it looked like this method had become obsolete...');
    }

    /**
     * $dsn has to contain db_name after the host. E.g. "mongodb://localhost:27017/mongo_test_db"
     *
     * @static
     *
     * @param $dsn
     * @param $user
     * @param $password
     *
     * @return \Mongo
     * @throws \Exception
     */
    public function __construct($dsn, $user, $password)
    {
        /* defining DB name */
        $this->dbName = substr($dsn, strrpos($dsn, '/') + 1);
        if (strlen($this->dbName) == 0) {
            throw new \Exception('Please specify valid $dsn with DB name after the host:port');
        }

        /* defining host */
        if (false !== strpos($dsn, 'mongodb://')) {
            $this->host = str_replace('mongodb://', '', $dsn);
        } else {
            $this->host = $dsn;
        }
        $this->host = rtrim(str_replace($this->dbName, '', $this->host), '/');

        $options = array(
            'connect' => true
        );

        if ($user && $password) {
            $options += array(
                'username' => $user,
                'password' => $password
            );
        }

        try {
            $m         = new \MongoClient($dsn, $options);
            $this->dbh = $m->selectDB($this->dbName);
        } catch (\MongoConnectionException $e) {
            throw new \Exception(sprintf('Failed to open Mongo connection: %s', $e->getMessage()));
        }

        $this->dsn      = $dsn;
        $this->user     = $user;
        $this->password = $password;
    }

    /**
     * @static
     *
     * @param $dsn
     * @param $user
     * @param $password
     *
     * @return MongoDb
     */
    public static function create($dsn, $user, $password)
    {
        return new MongoDb($dsn, $user, $password);
    }

    public function cleanup()
    {
        try {
            $list = $this->dbh->listCollections();
        } catch (\MongoException $e) {
            throw new \Exception(sprintf('Failed to list collections of the DB: %s', $e->getMessage()));
        }
        foreach ($list as $collection) {
            try {
                $collection->drop();
            } catch (\MongoException $e) {
                throw new \Exception(sprintf('Failed to drop collection: %s', $e->getMessage()));
            }
        }
    }

    /**
     * dump file has to be a javascript document where one can use all the mongo shell's commands
     * just FYI: this file can be easily created be RockMongo's export button
     *
     * @param $dumpFile
     */
    public function load($dumpFile)
    {
        if ($this->user && $this->password) {
            $cmd = sprintf(
                'mongo %s --username %s --password %s %s',
                $this->host . '/' . $this->dbName,
                $this->user,
                $this->password,
                $dumpFile
            );
        } else {
            $cmd = sprintf('mongo %s %s', $this->host . '/' . $this->dbName, $dumpFile);
        }
        shell_exec($cmd);
    }

    public function getDbh()
    {
        return $this->dbh;
    }
}