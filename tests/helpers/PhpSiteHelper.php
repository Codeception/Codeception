<?php
namespace Codeception\Module;

class PhpSiteHelper extends \Codeception\Lib\Framework
{
    public function __construct() {
        $this->client = new \Codeception\Lib\Connector\Universal();
        $this->client->setIndex(\Codeception\Configuration::dataDir().'/app/index.php');
    }
}
