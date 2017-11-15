<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleException as ModuleException;
use Codeception\Module as CodeceptionModule;
use Codeception\TestInterface;
use Interop\Amqp\AmqpConnectionFactory;
use Interop\Amqp\AmqpContext;
use Interop\Amqp\AmqpDestination;
use Interop\Amqp\AmqpMessage;
use Interop\Amqp\AmqpQueue;
use Interop\Amqp\AmqpTopic;
use Interop\Amqp\Impl\AmqpBind;
use Interop\Queue\InvalidDestinationException;

/**
 * This module interacts with message broker software that implements
 * the Advanced Message Queuing Protocol (AMQP) standard. For example, RabbitMQ (tested).
 *
 * <div class="alert alert-info">
 * To use this module with Composer you need a amqp interop compatible transport, for example <em>"enqueue/amqp-lib": "^0.8"</em>, <em>"enqueue/amqp-ext": "^0.8"</em> or <em>"enqueue/amqp-bunny": "^0.8"</em>package.
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
 * * factory_class - Contains an AMQP connection factory class.
 *
 * ### Example
 *
 *     modules:
 *         enabled:
 *             - AMQPInterop:
 *                 host: 'localhost'
 *                 port: '5672'
 *                 username: 'guest'
 *                 password: 'guest'
 *                 vhost: '/'
 *                 queues: [queue1, queue2]
 *                 single_channel: false
 *
 *                 # if you installed enqueue/amqp-lib package.
 *                 factory_class: \Enqueue\AmqpLib\AmqpConnectionFactory::class
 *
 * ## Public Properties
 *
 * * context - Interop\Amqp\AmqpContext - current context
 */
class AMQPInterop extends CodeceptionModule
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
     * @var AmqpContext
     */
    protected $context;

    protected $requiredFields = ['host', 'username', 'password', 'vhost', 'factory_class'];

    public function _initialize()
    {
        $factoryClass = $this->config['factory_class'];
        if (false === class_exists($factoryClass) ||
            false === (new \ReflectionClass($factoryClass))->implementsInterface(AmqpConnectionFactory::class)
        ) {
            throw new \LogicException(sprintf('The factory_class option has to be valid class that implements "%s"', AmqpConnectionFactory::class));
        }

        try {
            /** @var AmqpConnectionFactory $factory */
            $factory = new $factoryClass([
                'host' => $this->config['host'],
                'port' => $this->config['port'],
                'user' => $this->config['username'],
                'pass' => $this->config['password'],
                'vhost' => $this->config['vhost'],
            ]);

            $this->context = $factory->createContext();
        } catch (\Exception $e) {
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
        $this->pushToDestination($this->context->createTopic($exchange), $message, $routing_key);
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
        $this->pushToDestination($this->context->createQueue($queue), $message);

        //$this->getChannel()->queue_declare($queue);
    }

    private function pushToDestination(AmqpDestination $destination, $message, $routing_key = null)
    {
        $message = $message instanceof AmqpMessage
            ? $message
            : $this->context->createMessage($message);

        $message->setRoutingKey($routing_key);

        $this->context->createProducer()->send($destination, $message);
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
     * @return int
     */
    public function declareExchange(
        $exchange,
        $type,
        $passive = false,
        $durable = false,
        $auto_delete = true,
        $internal = false,
        $nowait = false,
        $arguments = null
    ) {
        $topic = $this->context->createTopic($exchange);
        $topic->setType($type);

        if ($passive) {
            $topic->addFlag(AmqpTopic::FLAG_PASSIVE);
        }
        if ($durable) {
            $topic->addFlag(AmqpTopic::FLAG_DURABLE);
        }
        if ($auto_delete) {
            $topic->addFlag(AmqpTopic::FLAG_AUTODELETE);
        }
        if ($internal) {
            $topic->addFlag(AmqpTopic::FLAG_INTERNAL);
        }
        if ($nowait) {
            $topic->addFlag(AmqpTopic::FLAG_NOWAIT);
        }
        if ($arguments) {
            $topic->setArguments($arguments);
        }

        return $this->context->declareTopic($topic);
    }

    /**
     * Declares queue, creates if needed
     *
     * ```php
     * <?php
     * $I->declareQueue(
     *     'nameOfMyQueue', // exchange name
     * )
     * ```
     *
     * @param string $queueName
     * @param bool $passive
     * @param bool $durable
     * @param bool $exclusive
     * @param bool $auto_delete
     * @param bool $nowait
     * @param array $arguments
     * @return int
     */
    public function declareQueue(
        $queueName = '',
        $passive = false,
        $durable = false,
        $exclusive = false,
        $auto_delete = true,
        $nowait = false,
        $arguments = null
    ) {
        $queue = $queueName ? $this->context->createQueue($queueName) : $this->context->createTemporaryQueue();

        if ($passive) {
            $queue->addFlag(AmqpQueue::FLAG_PASSIVE);
        }
        if ($durable) {
            $queue->addFlag(AmqpQueue::FLAG_DURABLE);
        }
        if ($exclusive) {
            $queue->addFlag(AmqpQueue::FLAG_EXCLUSIVE);
        }
        if ($auto_delete) {
            $queue->addFlag(AmqpQueue::FLAG_AUTODELETE);
        }
        if ($nowait) {
            $queue->addFlag(AmqpQueue::FLAG_NOWAIT);
        }
        if ($arguments) {
            $queue->setArguments($arguments);
        }

        return $this->context->declareQueue($queue);
        
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
     * @return mixed|null
     */
    public function bindQueueToExchange(
        $queue,
        $exchange,
        $routing_key = '',
        $nowait = false,
        $arguments = null
    ) {
        $source = $this->context->createTopic($exchange);
        $target = $this->context->createQueue($queue);
        $flags = AmqpBind::FLAG_NOPARAM;
        
        if ($nowait) {
            $flags |= AmqpBind::FLAG_NOWAIT;
        }
        
        $bind = new AmqpBind($target, $source, $routing_key, $flags, $arguments);
        
        return $this->context->bind($bind);
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
     * @param string $queueName
     * @param string $text
     */
    public function seeMessageInQueueContainsText($queueName, $text)
    {
        $queue = $this->context->createQueue($queueName);
        $consumer = $this->context->createConsumer($queue);
        
        $msg = $consumer->receiveNoWait();
        if (!$msg) {
            $this->fail("Message was not received");
        }
        
        $this->debugSection("Message", $msg->getBody());
        $this->assertContains($text, $msg->getBody());
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
     * @param string $queueName
     * @return AmqpMessage
     */
    public function grabMessageFromQueue($queueName)
    {
        $queue = $this->context->createQueue($queueName);
        $consumer = $this->context->createConsumer($queue);
        
        $message = $consumer->receiveNoWait();
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

        $queue = $this->context->createQueue($queueName);
        $this->context->purgeQueue($queue);
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

    protected function cleanup()
    {
        if (!isset($this->config['queues'])) {
            throw new ModuleException(__CLASS__, "please set queues for cleanup");
        }

        if (!$this->context) {
            return;
        }

        foreach ($this->config['queues'] as $queueName) {
            try {
                $queue = $this->context->createQueue($queueName);
                $this->context->purgeQueue($queue);
            } catch (InvalidDestinationException $e) {
                // ignore if exchange/queue doesn't exist
            }
        }
    }
}
