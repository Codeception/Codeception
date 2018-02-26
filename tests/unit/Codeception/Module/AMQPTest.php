<?php


class AMQPTest extends \PHPUnit\Framework\TestCase
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

    public function testPushToQueue()
    {
        $this->module->pushToQueue('queue1', 'hello');
        $this->module->seeMessageInQueueContainsText('queue1', 'hello');
    }

    public function testPushToExchange()
    {
        $queue = 'test-queue';
        $exchange = 'test-exchange';
        $topic = 'test.3';
        $message = 'test-message';

        $this->module->declareExchange($exchange, 'topic', false, true, false);
        $this->module->declareQueue($queue, false, true, false, false);
        $this->module->bindQueueToExchange($queue, $exchange, 'test.#');

        $this->module->pushToExchange($exchange, $message, $topic);
        $this->module->seeMessageInQueueContainsText($queue , $message);
    }
}
