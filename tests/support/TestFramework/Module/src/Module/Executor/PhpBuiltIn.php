<?php
namespace TestFramework\Module\src\Module\Executor;

use TestFramework\Module\src\Module\Source\PathInterface;

class PhpBuiltIn extends AbstractExecutor
{
    private $host = 'localhost';
    private $port = 0;

    const TYPE = 'phpBuiltIn';

    public function start(PathInterface $path)
    {
        $this->extractHostAndPort();

        $webServerMode = '';

        if (is_dir($path->getPath())) {
            $webServerMode = '-t';
        }

        $command = sprintf(
            'php -S %s:%d %s "%s" >/dev/null 2>&1 & echo $!',
            $this->host,
            $this->port,
            $webServerMode,
            $path->getPath()
        );

        $this->killServersByPort($this->port);

        $output = array();
        exec($command, $output, $returnValue);

        if ($returnValue) {
            die('Php built in server - execution failed');
        }

        $this->PID = (int)$output[0];
        echo sprintf('Start web-server %s:%d', $this->host, $this->port);

        return $this->PID;
    }

    public function restart(PathInterface $path)
    {
        $this->kill();
        $this->start($path, true);
    }

    public function kill()
    {
        $this->killServersByPort($this->port);
        return $this->PID;
    }

    private function killServersByPort($port)
    {
        exec("lsof -i tcp:" . $port . " | awk 'NR!=1 {print $2}' | xargs kill >/dev/null 2>&1");
    }

    private function extractHostAndPort()
    {
        switch ($this->config['configuration']) {
            case 'defined':
                $this->host = $this->config['host'];
                $this->port = (int)$this->config['port'];
                break;
        }
    }
}
