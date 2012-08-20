<?php

namespace Codeception\Module;

use Codeception\Exception\Module as ModuleException;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Exception\AMQPChannelException;

/**
 * @since 1.1.2
 * @author tiger.seo@gmail.com
 */
class AMQP extends \Codeception\Module
{
    const DEFAULT_PORT = 5672;

    protected $config = array(
        'cleanup' => true
    );

    /**
     * @var AMQPConnection
     */
    protected $_connection;

    protected $requiredFields = array('host', 'username', 'password', 'vhost');

    public function _initialize()
    {
        $host = $this->config['host'];
        $port = isset($this->config['port']) ? $this->config['port'] : self::DEFAULT_PORT;
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
