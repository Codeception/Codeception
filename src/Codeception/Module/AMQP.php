<?php

namespace Codeception\Module;

use Codeception\Exception\Module as ModuleException;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Exception\AMQPChannelException;

/**
 * This module interacts with message broker software that implements
 * the Advanced Message Queuing Protocol (AMQP) standard. For example, RabbitMQ.
 * Use it to cleanup the queue between tests.
 *
 * ## Config
 *
 * * cleanup: true - defined queues will be purged before running every test.
 *
 * ## Other
 *
 * @since 1.1.2
 * @author tiger.seo@gmail.com
 */
class AMQP extends \Codeception\Module
{
    const DEFAULT_PORT = 5672;

    protected $config = array(
        'cleanup' => true,
        'port' => self::DEFAULT_PORT
    );

    /**
     * @var AMQPConnection
     */
    protected $_connection;

    protected $requiredFields = array('host', 'username', 'password', 'vhost');

    public function _initialize()
    {
        $host = $this->config['host'];
        $port = $this->config['port'];
        $username = $this->config['username'];
        $password = $this->config['password'];
        $vhost = $this->config['vhost'];

        try {
            $this->_connection = new AMQPConnection($host, $port, $username, $password, $vhost);
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage() . ' while establishing connection to MQ server');
        }
    }

    public function _before(\Codeception\TestCase $test)
    {
        if ($this->config['cleanup']) {
            $this->cleanup();
        }
        parent::_before($test);
    }

    /**
     * Cleans up queue.
     */
    public function cleanupAMQP() {
        $this->cleanup();
    }

    protected function cleanup()
    {
        if (! isset($this->config['routes'])) {
            return;
        }

        $channel = $this->_connection->channel(1);

        foreach ($this->config['routes'] as $route) {
            try {
                $channel->queue_purge($route['queue']);
            } catch (AMQPChannelException $e) {
                # ignore if exchange/queue doesn't exist and rethrow exception if it's something else
                if ($e->getCode() !== 404) {
                    throw $e;
                }
            }
        }
    }
}