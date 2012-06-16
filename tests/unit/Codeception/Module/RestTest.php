<?php

use Codeception\Util\Stub as Stub;

class RestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\Module\REST
     */
    protected $module;

    public function setUp() {
        $this->module = new \Codeception\Module\REST();
        $connector = new \Codeception\Util\Connector\Universal();
        $connector->setIndex(\Codeception\Configuration::dataDir().'/rest/index.php');
        $this->module->client = $connector;
        $this->module->_before(Stub::makeEmpty('\Codeception\TestCase\Cest'));
        $this->module->client->setServerParameters(array(
            'SCRIPT_FILENAME' => 'index.php',
            'SCRIPT_NAME' => 'index',
            'SERVER_NAME' => 'localhost',
            'SERVER_PROTOCOL' => 'http'
        ));
    }

    public function testGet() {
        $this->module->sendGET('/rest/user/');
        $this->module->seeResponseIsJson();
        $this->module->seeResponseContains('davert');
        $this->module->seeResponseContainsJson(array('name' => 'davert'));
    }
    
    public function testPost() {
        $this->module->sendPOST('/rest/user/', array('name' => 'john'));
        $this->module->seeResponseContains('john');
        $this->module->seeResponseContainsJson(array('name' => 'john'));
    }
    
    public function testPut() {
        $this->module->sendPUT('/rest/user/', array('name' => 'laura'));
        $this->module->seeResponseContains('davert@mail.ua');
        $this->module->seeResponseContainsJson(array('name' => 'laura'));
        $this->module->dontSeeResponseContainsJson(array('name' => 'john'));
    }

}
