# Queue Module

**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/2.0/src/Codeception/Module/Queue.php)**



Works with Queue servers.

Testing with a selection of remote/local queueing services, including Amazon's SQS service
Iron.io service and beanstalkd service.

Supported and tested queue types are:

* [Iron.io](http://iron.io/)
* [Beanstalkd](http://kr.github.io/beanstalkd/)
* [Amazon SQS](http://aws.amazon.com/sqs/)

The following dependencies are needed for the listed queue servers:

* Beanstalkd: pda/pheanstalk ~2.0
* Amazon SQS: aws/aws-sdk-php
* IronMQ: iron-io/iron_mq

## Status

* Maintainer: **nathanmac**
* Stability:
    - Iron.io:    **stable**
    - Beanstalkd: **stable**
    - Amazon SQS: **stable**
* Contact: nathan.macnamara@outlook.com

## Config

The configuration settings depending on which queueing service is being used, all the options are listed
here. Refer to the configuration examples below to identify the configuration options required for your chosen
service.

* type - type of queueing server (defaults to beanstalkd).
* host - hostname/ip address of the queue server or the host for the iron.io when using iron.io service.
* port: 11300 - port number for the queue server.
* timeout: 90 - timeout settings for connecting the queue server.
* token - Iron.io access token.
* project - Iron.io project ID.
* key - AWS access key ID.
* secret - AWS secret access key.
* region - A region parameter is also required for AWS, refer to the AWS documentation for possible values list.

### Example
#### Example (beanstalkd)

    modules:
       enabled: [Queue]
       config:
          Queue:
             type: 'beanstalkd'
             host: '127.0.0.1'
             port: 11300
             timeout: 120

#### Example (Iron.io)

    modules:
       enabled: [Queue]
       config:
          Queue:
             'type' => 'iron',
             'host' => 'mq-aws-us-east-1.iron.io',
             'token' => 'your-token',
             'project' => 'your-project-id'

#### Example (AWS SQS)

    modules:
       enabled: [Queue]
       config:
          Queue:
             'type' => 'aws',
             'key' => 'your-public-key',
             'secret' => 'your-secret-key',
             'region' => 'us-west-2'



### addMessageToQueue
 
Add a message to a queue/tube

```php
<?php
$I->addMessageToQueue('this is a messages', 'default');
?>
```

 * `param string` $message Message Body
 * `param string` $queue Queue Name


### clearQueue
 
Clear all messages of the queue/tube

```php
<?php
$I->clearQueue('default');
?>
```

 * `param string` $queue Queue Name


### dontSeeEmptyQueue
 
Check if a queue/tube is NOT empty of all messages

```php
<?php
$I->dontSeeEmptyQueue('default');
?>
```

 * `param string` $queue Queue Name


### dontSeeQueueExists
 
Check if a queue/tube does NOT exist on the queueing server.

```php
<?php
$I->dontSeeQueueExists('default');
?>
```

 * `param string` $queue Queue Name


### dontSeeQueueHasCurrentCount
 
Check if a queue/tube does NOT have a given current number of messages

```php
<?php
$I->dontSeeQueueHasCurrentCount('default', 10);
?>
```

 * `param string` $queue Queue Name
 * `param int` $expected Number of messages expected


### dontSeeQueueHasTotalCount
 
Check if a queue/tube does NOT have a given total number of messages

```php
<?php
$I->dontSeeQueueHasTotalCount('default', 10);
?>
```

 * `param string` $queue Queue Name
 * `param int` $expected Number of messages expected


### grabQueueCurrentCount
 
Grabber method to get the current number of messages on the queue/tube (pending/ready)

```php
<?php
    $I->grabQueueCurrentCount('default');
?>
```
 * `param string` $queue Queue Name

@return int Count


### grabQueueTotalCount
 
Grabber method to get the total number of messages on the queue/tube

```php
<?php
    $I->grabQueueTotalCount('default');
?>
```

 * `param` $queue Queue Name

@return int Count


### grabQueues
 
Grabber method to get the list of queues/tubes on the server

```php
<?php
$queues = $I->grabQueues();
?>
```

@return array List of Queues/Tubes


### seeEmptyQueue
 
Check if a queue/tube is empty of all messages

```php
<?php
$I->seeEmptyQueue('default');
?>
```

 * `param string` $queue Queue Name


### seeQueueExists
 
Check if a queue/tube exists on the queueing server.

```php
<?php
$I->seeQueueExists('default');
?>
```

 * `param string` $queue Queue Name


### seeQueueHasCurrentCount
 
Check if a queue/tube has a given current number of messages

```php
<?php
$I->seeQueueHasCurrentCount('default', 10);
?>
```

 * `param string` $queue Queue Name
 * `param int` $expected Number of messages expected


### seeQueueHasTotalCount
 
Check if a queue/tube has a given total number of messages

```php
<?php
$I->seeQueueHasTotalCount('default', 10);
?>
```

 * `param string` $queue Queue Name
 * `param int` $expected Number of messages expected

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.0/src/Codeception/Module/Queue.php">Help us to improve documentation. Edit module reference</a></div>
