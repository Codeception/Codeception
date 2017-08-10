# Doctrine2


Access the database using [Doctrine2 ORM](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/).

When used with Zend Framework 2 or Symfony2, Doctrine's Entity Manager is automatically retrieved from Service Locator.
Set up your `functional.suite.yml` like this:

```
modules:
    enabled:
        - Symfony # 'ZF2' or 'Symfony'
        - Doctrine2:
            depends: Symfony
            cleanup: true # All doctrine queries will be wrapped in a transaction, which will be rolled back at the end of each test
```

If you don't use Symfony or Zend Framework, you need to specify a callback function to retrieve the Entity Manager:

```
modules:
    enabled:
        - Doctrine2:
            connection_callback: ['MyDb', 'createEntityManager']
            cleanup: true # All doctrine queries will be wrapped in a transaction, which will be rolled back at the end of each test

```

This will use static method of `MyDb::createEntityManager()` to establish the Entity Manager.

By default, the module will wrap everything into a transaction for each test and roll it back afterwards. By doing this
tests will run much faster and will be isolated from each other.

## Status

* Maintainer: **davert**
* Stability: **stable**
* Contact: codecept@davert.mail.ua

## Config

## Public Properties

* `em` - Entity Manager


## Actions

### dontSeeInRepository
 
Flushes changes to database and performs `findOneBy()` call for current repository.

 * `param` $entity
 * `param array` $params


### flushToDatabase
 
Performs $em->flush();


### grabEntitiesFromRepository
 
Selects entities from repository.
It builds query based on array of parameters.
You can use entity associations to build complex queries.

Example:

``` php
<?php
$users = $I->grabEntitiesFromRepository('AppBundle:User', array('name' => 'davert'));
?>
```

 * `Available since` 1.1
 * `param` $entity
 * `param array` $params
 * `return` array


### grabEntityFromRepository
 
Selects a single entity from repository.
It builds query based on array of parameters.
You can use entity associations to build complex queries.

Example:

``` php
<?php
$user = $I->grabEntityFromRepository('User', array('id' => '1234'));
?>
```

 * `Available since` 1.1
 * `param` $entity
 * `param array` $params
 * `return` object


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

 * `Available since` 1.1
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

This creates a stub class for Entity\User repository with redefined method findByUsername,
which will always return the NULL value.

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
 
Flushes changes to database, and executes a query with parameters defined in an array.
You can use entity associations to build complex queries.

Example:

``` php
<?php
$I->seeInRepository('AppBundle:User', array('name' => 'davert'));
$I->seeInRepository('User', array('name' => 'davert', 'Company' => array('name' => 'Codegyre')));
$I->seeInRepository('Client', array('User' => array('Company' => array('name' => 'Codegyre')));
?>
```

Fails if record for given criteria can\'t be found,

 * `param` $entity
 * `param array` $params

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.3/src/Codeception/Module/Doctrine2.php">Help us to improve documentation. Edit module reference</a></div>
