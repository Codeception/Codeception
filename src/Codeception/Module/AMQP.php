<?php

namespace Codeception\Module;

use Codeception\Exception\Module as ModuleException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPChannelException;

/**
 * This module interacts with message broker software that implements
 * the Advanced Message Queuing Protocol (AMQP) standard. For example, RabbitMQ (tested).
 * Use it to cleanup the queue between tests.
 *
 * ## Config
 *
 * * host: localhost - host to connect
 * * username: guest - username to connect
 * * password: guest - password to connect
 * * vhost: '/' - vhost to connect
 * * cleanup: true - defined queues will be purged before running every test.
 * * queues: [mail, twitter] - queues to cleanup
 *
 * ## Public Properties
 *
 * * connection - AMQPConnection - current connection
 * * channel - AMQPChannel - current channel
 *
 * @since 1.1.2
 * @author tiger.seo@gmail.com
 * @author davert
 */
class AMQP extends \Codeception\Module
{
    protected $config = array(
        'host' => 'locahost',
        'username' => 'guest',
        'password' => 'guest',
        'port' => '5672',
        'vhost' => '/',
        'cleanup' => true,
    );

    /**
     * @var AMQPConnection
     */
    public $connection;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    protected $requiredFields = array('host', 'username', 'password', 'vhost');

    public function _initialize()
    {
        $host = $this->config['host'];
        $port = $this->config['port'];
        $username = $this->config['username'];
        $password = $this->config['password'];
        $vhost = $this->config['vhost'];

        try {
            $this->connection = new AMQPConnection($host, $port, $username, $password, $vhost);
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage() . ' while establishing connection to MQ server');
        }
        $this->channel = $this->connection->channel();
    }

    public function _before(\Codeception\TestCase $test)
    {
        if ($this->config['cleanup']) {
            $this->cleanup();
        }
    }

    /**
     * Sends message to exchange
     *
     * ``` php
     * <?php
     * $I->pushToExchange('exchange.emails', 'thanks');
     * $I->pushToExchange('exchange.emails', new AMQPMessage('Thanks!'));
     * ?>
     * ```
     *
     * @param $exchange
     * @param $message string|AMQPMessage
     */
    public function pushToExchange($exchange, $message)
    {
        $message = $message instanceof AMQPMessage
            ? $message
            : new AMQPMessage($message);
        $this->channel->basic_publish($message, $exchange);
    }

    /**
     * Sends message to queue
     *
     * ``` php
     * <?php
     * $I->pushToQueue('queue.jobs', 'create user');
     * $I->pushToQueue('queue.jobs', new AMQPMessage('create'));
     * ?>
     * ```
     *
     * @param $queue
     * @param $message string|AMQPMessage
     */
    public function pushToQueue($queue, $message)
    {
        $message = $message instanceof AMQPMessage
            ? $message
            : new AMQPMessage($message);

        $this->channel->queue_declare($queue);
        $this->channel->basic_publish(new AMQPMessage($message), '',$queue);
    }

    /**
     * Checks if message containing text received.
     *
     * **This method drops message from queue**
     * **This method will wait for message. If none is sent the script will stuck**.
     * **Purges queue in the end**
     *
     * ``` php
     * <?php
     * $I->pushToQueue('queue.emails', 'Hello, davert');
     * $I->seeMessageInQueue('queue.emails','davert');
     * ?>
     * ```
     *
     * @param $queue
     * @param $message
     */
    public function seeMessageInQueue($queue, $message)
    {
        $this->channel->basic_consume($queue, '', false, false, false, false, function (AMQPMessage $msg) use ($message) {
            \PHPUnit_Framework_Assert::assertContains($message, $msg->body);
        });
        $this->debug("waiting for messages in queue '$queue''");
        $this->channel->wait();
        $this->channel->queue_purge($queue);
    }

    protected function cleanup()
    {
        if (! isset($this->config['queues'])) {
            throw new ModuleException(__CLASS__, "please set queues for cleanup");
        }

        $channel = $this->channel;
        
        foreach ($this->config['queues'] as $queue) {
            try {
                $channel->queue_purge($queue);
            } catch (AMQPChannelException $e) {
                # ignore if exchange/queue doesn't exist and rethrow exception if it's something else
                if ($e->getCode() !== 404) {
                    throw $e;
                }
            }
        }
    }
}