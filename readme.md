# Codeception [![Latest Stable](https://poser.pugx.org/Codeception/Codeception/version.png)](https://packagist.org/packages/Codeception/Codeception) [![Total Downloads](https://poser.pugx.org/codeception/codeception/downloads.png)](https://packagist.org/packages/codeception/codeception)

**Modern PHP Testing for everyone** 

Codeception is a modern full-stack testing framework for PHP.
Inspired by BDD, it provides you an absolutely new way of writing acceptance, functional and even unit tests.
Powered by PHPUnit 3.7.


| release |  branch  |  status  |
| ------- | -------- | -------- |
| **Stable** | **1.7** | [![Build Status](https://secure.travis-ci.org/Codeception/Codeception.png?branch=1.7)](http://travis-ci.org/Codeception/Codeception)
| **Current** | **1.8** | [![Build Status](https://secure.travis-ci.org/Codeception/Codeception.png?branch=1.8)](http://travis-ci.org/Codeception/Codeception) [![Dependencies Status](https://depending.in/Codeception/Codeception.png)](http://depending.in/Codeception/Codeception)
| **Edge** | **master** | [![Build Status](https://secure.travis-ci.org/Codeception/Codeception.png?branch=master)](http://travis-ci.org/Codeception/Codeception) [![Dependencies Status](https://depending.in/Codeception/Codeception.png)](http://depending.in/Codeception/Codeception)

#### Contributions

**Bugfixes should be sent to to current stable branch, which is the same as major version number.**
Breaking features and major improvements should be sent into `master`. When you send PRs to master, they will be added to release cycle only when the next stable branch is started.

### At a Glance

Describe what you test and how you test it. Use PHP to write descriptions faster.

Run tests and see what actions were taken and what results were seen.

#### Sample acceptance test

``` php
<?php

$I = new FunctionalTester($scenario);
$I->wantTo('create wiki page');
$I->amOnPage('/');
$I->click('Pages');
$I->click('New');
$I->see('New Page');
$I->submitForm('form#new_page', array('title' => 'Tree of Life Movie Review','body' => "Next time don't let Hollywood create art-house!"));
$I->see('page created'); // notice generated
$I->see('Tree of Life Movie Review','h1'); // head of page of is our title
$I->seeInCurrentUrl('pages/tree-of-life-movie-review'); // slug is generated
$I->seeInDatabase('pages', array('title' => 'Tree of Life Movie Review')); // data is stored in database
?>
```

For unit testing you can stay on classic PHPUnit tests, as Codeception can run them too.

## Documentation

[Documentation on Github](https://github.com/Codeception/Codeception/tree/master/docs)

Documentation is currently included within the project. Look for it in the 'docs' directory.

## Installation

### Phar

Download [codecept.phar](http://codeception.com/codecept.phar)

Copy it into your project.

Run CLI utility:

```
php codecept.phar
```

### Composer

```
php composer.phar require "codeception/codeception:*"
```

Read Also [Installation](http://codeception.com/install) | **[QuickStart](http://codeception.com/quickstart)**

## Getting Started

If you successfully installed Codeception, run this command:

```
codecept bootstrap
```

this will create a default directory structure and default test suites

See Documentation for more information.

### License
MIT

(c) Michael Bodnarchuk "Davert"
2011-2014
