# Codeception

[![Build Status](https://secure.travis-ci.org/Codeception/Codeception.png?branch=master)](http://travis-ci.org/Codeception/codeception)

Codeception is new PHP full-stack testing framework.
Inspired by BDD, it provides you absolutely new way for writing acceptance, functional and even unit tests.
Powered by PHPUnit 3.6.

### In a Glance

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
$I->submitForm('form#new_page', array('title' => 'Tree of Life Movie Review','body' => "Next time don't let Hollywood create arthouse!"));
$I->see('page created'); // notice generated
$I->see('Tree of Life Movie Review','h1'); // head of page of is our title
$I->seeInCurrentUrl('pages/tree-of-life-mobie-review'); // slug is generated
$I->seeInDatabase('pages', array('title' => 'Tree of Life Movie Review')); // data is stored in database
?>
```
Ok, as for unit test similar approach may seem weired, but...
Take a look at this:

#### Sample unit test

``` php
<?php
class UserControllerCest {
    public $class = 'UserController';

    public function createAction(CodeGuy $I)
    {
        $I->haveFakeClass($userController = Stub::makeEmptyExcept('UserController'));
        $I->executeTestedMethodOn($userController, array('username' => 'MilesDavis', 'email' => 'miles@davis.com'))
            ->seeResultEquals(true)
            ->seeMethodInvoked($userController, 'renderHtml')
            ->seeInDabatase('users', array('username' => 'MilesDavis'));
    }
}
?>

```

Anyway, If you don't really like writing unit tests in DSL, Codeceptance can run PHPUnit tests natively.

## Documentation

[Documentation on Github](https://github.com/Codeception/Codeception/tree/master/docs)

Documentation is currently bounded with project. Look for it in 'docs' directory.

## Installation

### PEAR
Install latest PEAR package from GitHub:

```
pear channel-discover codeception.com/pear
pear install codeception/Codeception
```

### Phar

Download [codecept.phar](https://github.com/Codeception/Codeception/raw/master/package/codecept.phar)

Copy it into your project.
Run CLI utility:

```
php codecept.phar
```

## Getting Started

If you sucessfully installed Codeception, run this commands:

```
codecept install
```

this will install all dependency tools like PHPUnit and Mink

```
codecept bootstrap
```

this will create default directory structure and default test suites

```
codecept build
```

This will generate Guy-classes, in order to make autocomplete works.

See Documentation for more information.

### License
MIT

(c) Michael Bodnarchuk "Davert"
2011