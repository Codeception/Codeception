# AMQP Module

**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/2.0/src/Codeception/Module/AMQP.php)**


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
        enabled: [AMQP]
        config:
            AMQP:
                host: 'localhost'
                port: '5672'
                username: 'guest'
                password: 'guest'
                vhost: '/'
                queues: [queue1, queue2]

## Public Properties

* connection - AMQPConnection - current connection

@since 1.1.2
@author tiger.seo@gmail.com
@author davert


### grabMessageFromQueue
 
Takes last message from queue.

$message = $I->grabMessageFromQueue('queue.emails');

 * `param` $queue
@return AMQPMessage


### pushToExchange
 
Sends message to exchange

``` php
<?php
$I->pushToExchange('exchange.emails', 'thanks');
$I->pushToExchange('exchange.emails', new AMQPMessage('Thanks!'));
?>
```

 * `param` $exchange
 * `param` $message string|AMQPMessage


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

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.0/src/Codeception/Module/AMQP.php">Help us to improve documentation. Edit module reference</a></div>
