# Doctrine1

Performs DB operations with Doctrine ORM 1.x

Uses active Doctrine connection. If none can be found will fail.

This module cleans all cached entities before each test.


## Actions


### seeInTable


Checks table contains row with specified values
Provide Doctrine model name can be passed to addWhere DQL

Example:

``` php
<?php
$I->seeInTable('User', array('name' => 'Davert', 'email' => 'davert * mail.com'));

```

 * param $model
 * param array $values

### dontSeeInTable


Checks table doesn't contain row with specified values
Provide Doctrine model name and criteria that can be passed to addWhere DQL

Example:

``` php
<?php
$I->dontSeeInTable('User', array('name' => 'Davert', 'email' => 'davert * mail.com'));

```

 * param $model
 * param array $values
