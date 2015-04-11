<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleException as ModuleException;
use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * This module interacts with message broker software that implements
 * the Advanced Message Queuing Protocol (AMQP) standard. For example, RabbitMQ (tested).
 * Use it to cleanup the queue between tests.
 *
 * <div class="alert alert-info">
 * To use this module with Composer you need <em>"videlalvaro/php-amqplib": "*"</em> package.
 * </div>
 *
 * ## Status
 * * Maintainer: **davert**, **tiger-seo**
 * * Stability: **alpha**
 * * Contact: codecept@davert.mail.ua
 * * Contact: tiger.seo@gmail.com
 *
 * *Please review the code of non-stable modules and provide patches if you have issues.*
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
 * ### Example
 *
 *     modules:
 *         enabled: [AMQP]
 *         config:
 *             AMQP:
 *                 host: 'localhost'
 *                 port: '5672'
 *                 username: 'guest'
 *                 password: 'guest'
 *                 vhost: '/'
 *                 queues: [queue1, queue2]
 *
 * ## Public Properties
 *
 * * connection - AMQPConnection - current connection
 *
 * @since 1.1.2
 * @author tiger.seo@gmail.com
 * @author davert
 */
class AMQP extends \Codeception\Module
{
    protected $config = [
        'host'     => 'locahost',
        'username' => 'guest',
        'password' => 'guest',
        'port'     => '5672',
        'vhost'    => '/',
        'cleanup'  => true,
    ];

    /**
     * @var AMQPConnection
     */
    public $connection;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    protected $requiredFields = ['host', 'username', 'password', 'vhost'];

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
        $this->connection->channel()->basic_publish($message, $exchange);
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

        $this->connection->channel()->queue_declare($queue);
        $this->connection->channel()->basic_publish($message, '', $queue);
    }

    /**
     * Checks if message containing text received.
     *
     * **This method drops message from queue**
     * **This method will wait for message. If none is sent the script will stuck**.
     *
     * ``` php
     * <?php
     * $I->pushToQueue('queue.emails', 'Hello, davert');
     * $I->seeMessageInQueueContainsText('queue.emails','davert');
     * ?>
     * ```
     *
     * @param $queue
     * @param $text
     */
    public function seeMessageInQueueContainsText($queue, $text)
    {
        $msg = $this->connection->channel()->basic_get($queue);
        if (!$msg) {
            $this->fail("Message was not received");
        }
        if (!$msg instanceof AMQPMessage) {
            $this->fail("Received message is not format of AMQPMessage");
        }
        $this->debugSection("Message", $msg->body);
        $this->assertContains($text, $msg->body);
    }

    /**
     * Takes last message from queue.
     *
     * $message = $I->grabMessageFromQueue('queue.emails');
     *
     * @param $queue
     * @return AMQPMessage
     */
    public function grabMessageFromQueue($queue)
    {
        $message = $this->connection->channel()->basic_get($queue);
        return $message;
    }

    protected function cleanup()
    {
        if (!isset($this->config['queues'])) {
            throw new ModuleException(__CLASS__, "please set queues for cleanup");
        }
        if (!$this->connection) {
            return;
        }
        foreach ($this->config['queues'] as $queue) {
            try {
                $this->connection->channel()->queue_purge($queue);
            } catch (\PhpAmqpLib\Exception\AMQPProtocolChannelException $e) {
                # ignore if exchange/queue doesn't exist and rethrow exception if it's something else
                if ($e->getCode() !== 404) {
                    throw $e;
                }
            }
        }
    }
}