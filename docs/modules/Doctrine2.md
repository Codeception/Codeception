# Doctrine2

Allows integration and testing for projects with Doctrine2 ORM.

Doctrine2 uses EntityManager to perform all database operations.
As the module uses active connection and active entity manager, instance of this object should be passed to this module.

It can be done in bootstrap file, by setting static $em property:

``` php
<?php

\Codeception\Module\Doctrine2::$em = $em

```


## Actions


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

### seeInRepository


Flushes changes to database and performs ->findOneBy() call for current repository.
Fails if record for given criteria can\'t be found,

 * param $entity
 * param array $params

### dontSeeInRepository


Flushes changes to database and performs ->findOneBy() call for current repository.

 * param $entity
 * param array $params
