<?php
namespace Codeception\Module;

class PhpSiteHelper extends \Codeception\Util\Framework
{
    public function __construct() {
        $this->client = new \Codeception\Util\Connector\Universal();
        $this->client->setIndex(\Codeception\Configuration::dataDir().'/app/index.php');
    }
}
