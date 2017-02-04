# AMQP


This module interacts with message broker software that implements
the Advanced Message Queuing Protocol (AMQP) standard. For example, RabbitMQ (tested).
Use it to cleanup the queue between tests.

<div class="alert alert-info">
To use this module with Composer you need <em>"videlalvaro/php-amqplib": "*"</em> package.
</div>

## Status
* Maintainer: **davert**, **tiger-seo**
* Stability: **alpha**
* Contact: codecept@davert.mail.ua
* Contact: tiger.seo@gmail.com

*Please review the code of non-stable modules and provide patches if you have issues.*

## Config

* host: localhost - host to connect
* username: guest - username to connect
* password: guest - password to connect
* vhost: '/' - vhost to connect
* cleanup: true - defined queues will be purged before running every test.
* queues: [mail, twitter] - queues to cleanup

### Example

    modules:
        enabled:
            - AMQP:
                host: 'localhost'
                port: '5672'
                username: 'guest'
                password: 'guest'
                vhost: '/'
                queues: [queue1, queue2]

## Public Properties

* connection - AMQPStreamConnection - current connection

@since 1.1.2
@author tiger.seo@gmail.com
@author davert


## Actions

### bindQueueToExchange
 
Binds a queue to an exchange

This is an alias of method `queue_bind` of `PhpAmqpLib\Channel\AMQPChannel`.

```php
<?php
$I->bindQueueToExchange(
    'nameOfMyQueueToBind', // name of the queue
    'transactionTracking.transaction', // exchange name to bind to
    'your.routing.key' // Optionally, provide a binding key
    //.. see the original method for more options
)
```


### declareExchange
 
Declares an exchange

This is an alias of method `exchange_declare` of `PhpAmqpLib\Channel\AMQPChannel`.

```php
<?php
$I->declareExchange(
    'nameOfMyExchange', // exchange name
    'topic' // exchange type
    //.. see the original method for more options
)
```


### declareQueue
 
Declares a queue

This is an alias of method `queue_declare` of `PhpAmqpLib\Channel\AMQPChannel`.

```php
<?php
$I->declareQueue(
    'nameOfMyQueue', // exchange name
    //.. see the original method for more options
)
```


### grabMessageFromQueue
 
Takes last message from queue.

$message = $I->grabMessageFromQueue('queue.emails');

 * `param` $queue
 * `return` AMQPMessage


### purgeAllQueues
 
Purge all queues defined in config.

``` php
<?php
$I->purgeAllQueues();
?>
```


### purgeQueue
 
Purge a specific queue defined in config.

``` php
<?php
$I->purgeQueue('queue.emails');
?>
```


### pushToExchange
 
Sends message to exchange by sending exchange name, message
and (optionally) a routing key

``` php
<?php
$I->pushToExchange('exchange.emails', 'thanks');
$I->pushToExchange('exchange.emails', new AMQPMessage('Thanks!'));
$I->pushToExchange('exchange.emails', new AMQPMessage('Thanks!'), 'severity');
?>
```

 * `param` $exchange
 * `param` $message string|AMQPMessage
 * `param` $routing_key


### pushToQueue
 
Sends message to queue

``` php
<?php
$I->pushToQueue('queue.jobs', 'create user');
$I->pushToQueue('queue.jobs', new AMQPMessage('create'));
?>
```

 * `param` $queue
 * `param` $message string|AMQPMessage


### seeMessageInQueueContainsText
 
Checks if message containing text received.

**This method drops message from queue**
**This method will wait for message. If none is sent the script will stuck**.

``` php
<?php
$I->pushToQueue('queue.emails', 'Hello, davert');
$I->seeMessageInQueueContainsText('queue.emails','davert');
?>
```

 * `param` $queue
 * `param` $text

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.2/src/Codeception/Module/AMQP.php">Help us to improve documentation. Edit module reference</a></div>
