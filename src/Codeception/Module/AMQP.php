<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleException as ModuleException;
use Codeception\Lib\Interfaces\RequiresPackage;
use Codeception\Module as CodeceptionModule;
use Codeception\TestInterface;
use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * This module interacts with message broker software that implements
 * the Advanced Message Queuing Protocol (AMQP) standard. For example, RabbitMQ (tested).
 *
 * <div class="alert alert-info">
 * To use this module with Composer you need <em>"php-amqplib/php-amqplib": "~2.4"</em> package.
 * </div>
 *
 * ## Config
 *
 * * host: localhost - host to connect
 * * username: guest - username to connect
 * * password: guest - password to connect
 * * vhost: '/' - vhost to connect
 * * cleanup: true - defined queues will be purged before running every test.
 * * queues: [mail, twitter] - queues to cleanup
 * * single_channel - create and use only one channel during test execution
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
 *                 single_channel: false
 *
 * ## Public Properties
 *
 * * connection - AMQPStreamConnection - current connection
 */
class AMQP extends CodeceptionModule implements RequiresPackage
{
    protected $config = [
        'host'           => 'localhost',
        'username'       => 'guest',
        'password'       => 'guest',
        'port'           => '5672',
        'vhost'          => '/',
        'cleanup'        => true,
        'single_channel' => false
    ];

    /**
     * @var AMQPStreamConnection
     */
    public $connection;

    /**
     * @var int
     */
    protected $channelId;

    protected $requiredFields = ['host', 'username', 'password', 'vhost'];

    public function _requires()
    {
        return ['PhpAmqpLib\Connection\AMQPStreamConnection' => '"php-amqplib/php-amqplib": "~2.4"'];
    }

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

    public function _before(TestInterface $test)
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
     * @param string $exchange
     * @param string|\PhpAmqpLib\Message\AMQPMessage $message
     * @param string $routing_key
     */
    public function pushToExchange($exchange, $message, $routing_key = null)
    {
        $message = $message instanceof AMQPMessage
            ? $message
            : new AMQPMessage($message);
        $this->getChannel()->basic_publish($message, $exchange, $routing_key);
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
     * @param string $queue
     * @param string|\PhpAmqpLib\Message\AMQPMessage $message
     */
    public function pushToQueue($queue, $message)
    {
        $message = $message instanceof AMQPMessage
            ? $message
            : new AMQPMessage($message);

        $this->getChannel()->queue_declare($queue);
        $this->getChannel()->basic_publish($message, '', $queue);
    }

    /**
     * Declares an exchange
     *
     * This is an alias of method `exchange_declare` of `PhpAmqpLib\Channel\AMQPChannel`.
     *
     * ```php
     * <?php
     * $I->declareExchange(
     *     'nameOfMyExchange', // exchange name
     *     'topic' // exchange type
     * )
     * ```
     *
     * @param string $exchange
     * @param string $type
     * @param bool $passive
     * @param bool $durable
     * @param bool $auto_delete
     * @param bool $internal
     * @param bool $nowait
     * @param array $arguments
     * @param int $ticket
     * @return mixed|null
     */
    public function declareExchange(
        $exchange,
        $type,
        $passive = false,
        $durable = false,
        $auto_delete = true,
        $internal = false,
        $nowait = false,
        $arguments = null,
        $ticket = null
    ) {
        return $this->getChannel()->exchange_declare(
            $exchange,
            $type,
            $passive,
            $durable,
            $auto_delete,
            $internal,
            $nowait,
            $arguments,
            $ticket
        );
    }

    /**
     * Declares queue, creates if needed
     *
     * This is an alias of method `queue_declare` of `PhpAmqpLib\Channel\AMQPChannel`.
     *
     * ```php
     * <?php
     * $I->declareQueue(
     *     'nameOfMyQueue', // exchange name
     * )
     * ```
     *
     * @param string $queue
     * @param bool $passive
     * @param bool $durable
     * @param bool $exclusive
     * @param bool $auto_delete
     * @param bool $nowait
     * @param array $arguments
     * @param int $ticket
     * @return mixed|null
     */
    public function declareQueue(
        $queue = '',
        $passive = false,
        $durable = false,
        $exclusive = false,
        $auto_delete = true,
        $nowait = false,
        $arguments = null,
        $ticket = null
    ) {
        return $this->getChannel()->queue_declare(
            $queue,
            $passive,
            $durable,
            $exclusive,
            $auto_delete,
            $nowait,
            $arguments,
            $ticket
        );
    }

    /**
     * Binds a queue to an exchange
     *
     * This is an alias of method `queue_bind` of `PhpAmqpLib\Channel\AMQPChannel`.
     *
     * ```php
     * <?php
     * $I->bindQueueToExchange(
     *     'nameOfMyQueueToBind', // name of the queue
     *     'transactionTracking.transaction', // exchange name to bind to
     *     'your.routing.key' // Optionally, provide a binding key
     * )
     * ```
     *
     * @param string $queue
     * @param string $exchange
     * @param string $routing_key
     * @param bool $nowait
     * @param array $arguments
     * @param int $ticket
     * @return mixed|null
     */
    public function bindQueueToExchange(
        $queue,
        $exchange,
        $routing_key = '',
        $nowait = false,
        $arguments = null,
        $ticket = null
    ) {
        return $this->getChannel()->queue_bind(
            $queue,
            $exchange,
            $routing_key,
            $nowait,
            $arguments,
            $ticket
        );
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
     * @param string $queue
     * @param string $text
     */
    public function seeMessageInQueueContainsText($queue, $text)
    {
        $msg = $this->getChannel()->basic_get($queue);
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
     * ``` php
     * <?php
     * $message = $I->grabMessageFromQueue('queue.emails');
     * ?>
     * ```
     *
     * @param string $queue
     * @return \PhpAmqpLib\Message\AMQPMessage
     */
    public function grabMessageFromQueue($queue)
    {
        $message = $this->getChannel()->basic_get($queue);
        return $message;
    }

    /**
     * Purge a specific queue defined in config.
     *
     * ``` php
     * <?php
     * $I->purgeQueue('queue.emails');
     * ?>
     * ```
     *
     * @param string $queueName
     */
    public function purgeQueue($queueName = '')
    {
        if (! in_array($queueName, $this->config['queues'])) {
            throw new ModuleException(__CLASS__, "'$queueName' doesn't exist in queues config list");
        }

        $this->getChannel()->queue_purge($queueName, true);
    }

    /**
     * Purge all queues defined in config.
     *
     * ``` php
     * <?php
     * $I->purgeAllQueues();
     * ?>
     * ```
     */
    public function purgeAllQueues()
    {
        $this->cleanup();
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    protected function getChannel()
    {
        if ($this->config['single_channel'] && $this->channelId === null) {
            $this->channelId = $this->connection->get_free_channel_id();
        }
        return $this->connection->channel($this->channelId);
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
                $this->getChannel()->queue_purge($queue);
            } catch (AMQPProtocolChannelException $e) {
                // ignore if exchange/queue doesn't exist and rethrow exception if it's something else
                if ($e->getCode() !== 404) {
                    throw $e;
                }
            }
        }
    }
}
