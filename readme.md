# Codeception

Codeception is new PHP full-stack testing framework.
Inspired by BDD, it provides you absolutely new way for writing acceptance, functional and even unit tests.
Powered by PHPUnit 3.6.

Previously called 'TestGuy'. Now is extended to feature CodeGuy, TestGuy, and WebGuy in one pack.

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
$I->submitForm('#pageForm', array('page' => array(
    'title' => 'Tree of Life Movie Review',
    'body' => 'Next time don\'t let Hollywood create arthouse =) '
)));
$I->see('page created'); // notice generated
$I->see('Tree of Life Movie Review','h1'); // head of page of is our title
$I->seeInCurrentUrl('pages/tree-of-life-mobie-review'); // slug is generated
$I->seeInDatabase('pages', array('title' => 'Tree of Life Movie Review')); // data is stored in database

```
Ok, as for unit test similar approach may seem weired, but...
Take a look at this:

#### Sample unit test

``` php
<?php

$I = new CodeGuy($scenario);
$I->wantTo('create new user from controller');
$I->testMethod('UserController.createAction');
$I->haveFakeClass($userController = Stub::make('UserController'));
$I->executeTestedMethodOn($userController, array('username' => 'MilesDavis', 'email' => 'miles@davis.com'));
$I->seeResultEquals(true);
$I->seeInDabatase('users', array('username' => 'MilesDavis'));

```

Anyway, If you don't really like writing unit tests in DSL, Codeceptance can run PHPUnit tests natively.

### Documentation

[Documentation on Github](https://github.com/DavertMik/Codeception/tree/master/docs)

Documentation is currently bounded with project. Look for it in 'docs' directory.


### Installation

#### Clone

Clone Codeception into your project.

``` git clone git://github.com/DavertMik/Codeception.git vendor/

Run CLI utility:

``` php vendor/Codeception/codecpt

### PEAR (edge)

Currently we don't have PEAR channel avaible, but PEAR installation is preferred for unstable releases.

Enter you PEAR dir and clone this repository into it.

Create batch or sh file to call CLI utility

``` php %PATH-TO-INSTALLATION%/Codeception/codecept

#### Phar

Can be installed by downloading phar executable.

Download (codecept.phar)[https://github.com/DavertMik/Codeception/raw/master/package/codecept.phar]

Copy it into your project.

Execute, to run CLI utility

``` php codecept.phar

### Getting Started

If you sucessfully installed Codeception, and you have CLI utility running in your project's root, run this commands:

``` codecept install
this will install all dependency tools like PHPUnit and Mink

``` codecept bootstrap
this will create default directory structure and default test suites

``` codecept build

This will generate Guy-classes, in order to make autocomplete works.


See Documentation for more information.

### License
MIT

(c) Michael Bodnarchuk "Davert"
2011