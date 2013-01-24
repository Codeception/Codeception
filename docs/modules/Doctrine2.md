# Doctrine2 Module
**For additional reference,, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Doctrine2)**
Allows integration and testing for projects with Doctrine2 ORM.

Doctrine2 uses EntityManager to perform all database operations.
As the module uses active connection and active entity manager, instance of this object should be passed to this module.

It can be done in bootstrap file, by setting static $em property:

``` php
<?php

\Codeception\Module\Doctrine2::$em = $em

```
## Status

* Maintainer: **davert**
* stability: stable
* Contact: codecept@davert.mail.ua

## Config

* auto_connect: true - tries to get EntityManager through connected frameworks. If none found expects the $em values specified as discribed above.
* cleanup: true - all doctrine queries will be run in transaction, which will be rolled back at the end of test.

## Actions


### dontSeeInRepository


Flushes changes to database and performs ->findOneBy() call for current repository.

 * param $entity
 * param array $params


### flushToDatabase


Performs $em->flush();


### haveFakeRepository


Mocks the repository.

With this action you can redefine any method of any repository.
Please, note: this fake repositories will be accessible through entity manager till the end of test.

Example:

``` php
<?php

$I->haveFakeRepository('Entity\User', array('findByUsername' => function($username) {  return null; }));

```

This creates a stub class for Entity\User repository with redefined method findByUsername, which will always return the NULL value.

 * param $classname
 * param array $methods


### persistEntity


Adds entity to repository and flushes. You can redefine it's properties with the second parameter.

Example:

``` php
<?php
$I->persistEntity($user, array('name' => 'Miles'));
```

 * param $obj
 * param array $values


### seeInRepository


Flushes changes to database executes a query defined by array.
It builds query based on array of parameters.
You can use entity associations to build complex queries.

Example:

``` php
<?php
$I->seeInRepository('User', array('name' => 'davert'));
$I->seeInRepository('User', array('name' => 'davert', 'Company' => array('name' => 'Codegyre')));
$I->seeInRepository('Client', array('User' => array('Company' => array('name' => 'Codegyre')));
?>
```

Fails if record for given criteria can\'t be found,

 * param $entity
 * param array $params
