<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\Exception\ModuleException as ModuleException;
use Codeception\TestCase;
use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;

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
 *         enabled:
 *             - AMQP:
 *                 host: 'localhost'
 *                 port: '5672'
 *                 username: 'guest'
 *                 password: 'guest'
 *                 vhost: '/'
 *                 queues: [queue1, queue2]
 *
 * ## Public Properties
 *
 * * connection - AMQPStreamConnection - current connection
 *
 * @since 1.1.2
 * @author tiger.seo@gmail.com
 * @author davert
 */
class AMQP extends CodeceptionModule
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
     * @var AMQPStreamConnection
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
            $this->connection = new AMQPStreamConnection($host, $port, $username, $password, $vhost);
        } catch (Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage() . ' while establishing connection to MQ server');
        }
    }

    public function _before(TestCase $test)
    {
        if ($this->config['cleanup']) {
            $this->cleanup();
        }
    }

    /**
     * Sends message to exchange by sending exchange name, message
     * and (optionally) a routing key
     * 
     * ``` php
     * <?php
     * $I->pushToExchange('exchange.emails', 'thanks');
     * $I->pushToExchange('exchange.emails', new AMQPMessage('Thanks!'));
     * $I->pushToExchange('exchange.emails', new AMQPMessage('Thanks!'), 'severity');
     * ?>
     * ```
     *
     * @param $exchange
     * @param $message string|AMQPMessage
     * @param $routing_key
     */
    public function pushToExchange($exchange, $message, $routing_key = null)
    {
        $message = $message instanceof AMQPMessage
            ? $message
            : new AMQPMessage($message);
        $this->connection->channel()->basic_publish($message, $exchange, $routing_key);
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
            } catch (AMQPProtocolChannelException $e) {
                // ignore if exchange/queue doesn't exist and rethrow exception if it's something else
                if ($e->getCode() !== 404) {
                    throw $e;
                }
            }
        }
    }
}
