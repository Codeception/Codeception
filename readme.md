# Codeception

[![Build Status](https://secure.travis-ci.org/Codeception/Codeception.png?branch=master)](http://travis-ci.org/Codeception/Codeception)

Codeception is a new PHP full-stack testing framework.
Inspired by BDD, it provides you an absolutely new way of writing acceptance, functional and even unit tests.
Powered by PHPUnit 3.7.

### At a Glance

Describe what you test and how you test it. Use PHP to write descriptions faster.

Run tests and see what actions were taken and what results were seen.

#### Sample acceptance test

``` php
<?php

$I = new TestGuy($scenario);
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

Download [codecept.phar](https://github.com/Codeception/Codeception/raw/master/package/codecept.phar)

Copy it into your project.

Run CLI utility:

```
php codecept.phar
```

## Getting Started

If you successfully installed Codeception, run this command:

```
codecept bootstrap
```

this will create a default directory structure and default test suites

```
codecept build
```

This will generate Guy-classes, in order to make autocomplete work.

See Documentation for more information.

### License
MIT

(c) Michael Bodnarchuk "Davert"
2011-2013
