


Allows integration and testing for projects with Doctrine2 ORM.
Doctrine2 uses EntityManager to perform all database operations.

When using with Zend Framework 2 or Symfony2 Doctrine connection is automatically retrieved from Service Locator.
In this case you should include either **Symfony2** or **ZF2** module and specify it as dependent for Doctrine:

```
modules:
    enabled:
        - Symfony2
        - Doctrine2:
            depends: Symfony2
```

If you don't use any of frameworks above, you should specify a callback function to receive entity manager:

```
modules:
    enabled:
        - Doctrine2:
            connection_callback: ['MyDb', 'createEntityManager']

```

This will use static method of `MyDb::createEntityManager()` to establish EntityManager.

By default module will wrap everything into transaction for each test and rollback it afterwards. By doing this
tests won't write anything to database, and so will run much faster and will be isolate dfrom each other.
This behavior can be changed by specifying `cleanup: false` in config.

## Status

* Maintainer: **davert**
* Stability: **stable**
* Contact: codecept@davert.mail.ua

## Config

* cleanup: true - all doctrine queries will be run in transaction, which will be rolled back at the end of test.
* connection_callback: - callable that will return an instance of EntityManager. This is a must if you run Doctrine without Zend2 or Symfony2 frameworks

 ### Example (`functional.suite.yml`)

     modules:
        enabled: [Doctrine2]
        config:
           Doctrine2:
              cleanup: false

## Public Properties

* `em` - Entity Manager


### dontSeeInRepository
 
Flushes changes to database and performs ->findOneBy() call for current repository.

 * `param` $entity
 * `param array` $params


### flushToDatabase
 
Performs $em->flush();


### grabFromRepository
 
Selects field value from repository.
It builds query based on array of parameters.
You can use entity associations to build complex queries.

Example:

``` php
<?php
$email = $I->grabFromRepository('User', 'email', array('name' => 'davert'));
?>
```

@version 1.1
 * `param` $entity
 * `param` $field
 * `param array` $params
 * `return` array


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

 * `param` $classname
 * `param array` $methods


### haveInRepository
 
Persists record into repository.
This method crates an entity, and sets its properties directly (via reflection).
Setters of entity won't be executed, but you can create almost any entity and save it to database.
Returns id using `getId` of newly created entity.

```php
$I->haveInRepository('Entity\User', array('name' => 'davert'));
```


### persistEntity
 
Adds entity to repository and flushes. You can redefine it's properties with the second parameter.

Example:

``` php
<?php
$I->persistEntity(new \Entity\User, array('name' => 'Miles'));
$I->persistEntity($user, array('name' => 'Miles'));
```

 * `param` $obj
 * `param array` $values


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

 * `param` $entity
 * `param array` $params

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.1/src/Codeception/Module/Doctrine2.php">Help us to improve documentation. Edit module reference</a></div>
