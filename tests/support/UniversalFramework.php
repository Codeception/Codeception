<?php
namespace Codeception\Module;

class UniversalFramework extends \Codeception\Lib\Framework
{
    public function __construct()
    {
        $index = '/app/index.php';
        $this->client = new \Codeception\Lib\Connector\Universal();
        $this->client->setIndex(\Codeception\Configuration::dataDir().$index);
    }
}
