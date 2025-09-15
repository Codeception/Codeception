<?php
namespace Codeception\Lib\Driver;

class Sqlite extends Db
{

    protected $hasSnapshot = false;
    protected $filename = '';
    protected $con = null;

    public function __construct($dsn, $user, $password)
    {
        parent::__construct($dsn, $user, $password);
        $this->filename = \Codeception\Configuration::projectDir() . substr($this->dsn, 7);
        $this->dsn      = 'sqlite:' . $this->filename;
    }
    
    private function sanitizeFilename($filename)
    {
        $projectDir = \Codeception\Configuration::projectDir();
        $base = basename($filename);
        $realPath = realpath($projectDir . '/' . $base);
        if ($realPath === false || strpos($realPath, $projectDir) !== 0) {
            throw new \RuntimeException('Invalid database filename');
        }
        return $realPath;
    }
    
    public function cleanup()
    {
        $safeFilename = $this->sanitizeFilename($this->filename);
        $this->dbh = null;
        file_put_contents($safeFilename, '');
        $this->dbh = self::connect($this->dsn, $this->user, $this->password);
    }

    public function load($sql)
    {
        $safeFilename = $this->sanitizeFilename($this->filename);
        $safeSnapshot = $safeFilename . '_snapshot';

        if ($this->hasSnapshot) {
            $this->dbh = null;
            file_put_contents($safeFilename, file_get_contents($safeSnapshot));
            $this->dbh = new \PDO($this->dsn, $this->user, $this->password);
        } else {
            if (file_exists($safeSnapshot)) {
                unlink($safeSnapshot);
            }
            parent::load($sql);
            copy($safeFilename, $safeSnapshot);
            $this->hasSnapshot = true;
        }
    }

    /**
     * @param string $tableName
     *
     * @return string
     */
    public function getPrimaryColumn($tableName)
    {
        // @TODO: Implement this for SQLite later
        return 'id';
    }
}
