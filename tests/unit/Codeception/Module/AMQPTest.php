<?php


class AMQPTest extends \PHPUnit_Framework_TestCase
{
    protected $config = array(
        'host'     => 'localhost',
        'username' => 'guest',
        'password' => 'guest',
        'port' => '5672',
        'vhost'    => '/',
        'cleanup' => false,
        'queues' => array('queue1')
    );

    /**
     * @var \Codeception\Module\AMQP
     */
    protected $module = null;

    public function setUp()
    {
        $this->module = new \Codeception\Module\AMQP(make_container());
        $this->module->_setConfig($this->config);
        $res = @stream_socket_client('tcp://localhost:5672');
        if ($res === false) {
            $this->markTestSkipped('AMQP is not running');
        }

        $this->module->_initialize();
        $connection = $this->module->connection;
        $connection->channel()->queue_declare('queue1');
    }

    public function testQueueUsage()
    {
        $this->module->pushToQueue('queue1', 'hello');
        $this->module->seeMessageInQueueContainsText('queue1', 'hello');
    }
}
