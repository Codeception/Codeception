<?php

use Codeception\Util\Stub as Stub;

class AMQPTest extends \PHPUnit_Framework_TestCase
{
    protected $config = array(
        'host'     => 'localhost',
        'username' => 'guest',
        'password' => 'guest',
        'vhost'    => '/',
        'routes'   => array(
            array('exchange' => 'Test', 'queue' => 'Test')
        )
    );

    /**
     * @var \Codeception\Module\AMQP
     */
    protected $module = null;

    public function setUp()
    {
        $this->module = new \Codeception\Module\AMQP;
        $this->module->_setConfig($this->config);
        try {
            stream_socket_client('tcp://localhost:5672');
        } catch (\ErrorException $e) {
            $this->markTestSkipped('AMQP is not running');
        }
        $this->module->_initialize();
        $this->module->_before(Stub::makeEmpty('\Codeception\TestCase\Cest'));
    }

    public function testCleanup()
    {
        $this->markTestIncomplete();
    }
}
