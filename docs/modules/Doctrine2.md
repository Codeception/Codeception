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

## Note on parameters

Every method that expects some parameters to be checked against values in the database (`see...()`,
`dontSee...()`, `grab...()`) can accept instance of \Doctrine\Common\Collections\Criteria for more
flexibility, e.g.:

``` php
$I->seeInRepository('User', [
    'name' => 'John',
    Criteria::create()->where(
        Criteria::expr()->endsWith('email', '@domain.com')
    ),
]);
```

If criteria is just a `->where(...)` construct, you can pass just expression without criteria wrapper:

``` php
$I->seeInRepository('User', [
    'name' => 'John',
    Criteria::expr()->endsWith('email', '@domain.com'),
]);
```

Criteria can be used not only to filter data, but also to change order of results:

``` php
$I->grabEntitiesFromRepository('User', [
    'status' => 'active',
    Criteria::create()->orderBy(['name' => 'asc']),
]);
```

Note that key is ignored, because actual field name is part of criteria and/or expression.

## Actions

### clearEntityManager
 
Performs $em->clear():

``` php
$I->clearEntityManager();
```


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
 * `param array` $params. For `IS NULL`, use `array('field'=>null)`
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
 * `param array` $params. For `IS NULL`, use `array('field'=>null)`
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
This method creates an entity, and sets its properties directly (via reflection).
Setters of entity won't be executed, but you can create almost any entity and save it to database.
If the entity has a constructor, for optional parameters the default value will be used and for non-optional parameters the given fields (with a matching name) will be passed when calling the constructor before the properties get set directly (via reflection).

Returns primary key of newly created entity. Primary key value is extracted using Reflection API.
If primary key is composite, array of values is returned.

```php
$I->haveInRepository('Entity\User', array('name' => 'davert'));
```

This method also accepts instances as first argument, which is useful when entity constructor
has some arguments:

```php
$I->haveInRepository(new User($arg), array('name' => 'davert'));
```

Alternatively, constructor arguments can be passed by name. Given User constructor signature is `__constructor($arg)`, the example above could be rewritten like this:

```php
$I->haveInRepository('Entity\User', array('arg' => $arg, 'name' => 'davert'));
```

If entity has relations, they can be populated too. In case of OneToMany the following format
ie expected:

```php
$I->haveInRepository('Entity\User', array(
    'name' => 'davert',
    'posts' => array(
        array(
            'title' => 'Post 1',
        ),
        array(
            'title' => 'Post 2',
        ),
    ),
));
```

For ManyToOne format is slightly different:

```php
$I->haveInRepository('Entity\User', array(
    'name' => 'davert',
    'post' => array(
        'title' => 'Post 1',
    ),
));
```

This works recursively, so you can create deep structures in a single call.

Note that both `$em->persist(...)`, $em->refresh(...), and `$em->flush()` are called every time.

 * `param string|object` $classNameOrInstance
 * `param array` $data


### loadFixtures
 
Loads fixtures. Fixture can be specified as a fully qualified class name,
an instance, or an array of class names/instances.

```php
<?php
$I->loadFixtures(AppFixtures::class);
$I->loadFixtures([AppFixtures1::class, AppFixtures2::class]);
$I->loadFixtures(new AppFixtures);
```

By default fixtures are loaded in 'append' mode. To replace all
data in database, use `false` as second parameter:

```php
<?php
$I->loadFixtures(AppFixtures::class, false);
```

Note: this method requires `doctrine/data-fixtures` package to be installed.

 * `param string|string[]|object[]` $fixtures
 * `param bool` $append
@throws ModuleException
@throws ModuleRequireException


### onReconfigure
 
HOOK to be executed when config changes with `_reconfigure`.


### persistEntity
 
This method is deprecated in favor of `haveInRepository()`. It's functionality is exactly the same.


### refreshEntities
 
Performs $em->refresh() on every passed entity:

``` php
$I->refreshEntities($user);
$I->refreshEntities([$post1, $post2, $post3]]);
```

This can useful in acceptance tests where entity can become invalid due to
external (relative to entity manager used in tests) changes.

 * `param object|object[]` $entities


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

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/3.0/src/Codeception/Module/Doctrine2.php">Help us to improve documentation. Edit module reference</a></div>
