# AMQP Module

This module interacts with message broker software that implements
the Advanced Message Queuing Protocol (AMQP) standard. For example, RabbitMQ.
Use it to cleanup the queue between tests.

## Config

* cleanup: true - defined queues will be purged before running every test.

## Other

 * available since version 1.1.2
 * author tiger.seo@gmail.com

## Actions


### cleanupAMQP


Cleans up queue.
