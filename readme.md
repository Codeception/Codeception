# Codeception

[![Latest Stable](https://poser.pugx.org/Codeception/Codeception/version.png)](https://packagist.org/packages/Codeception/Codeception)
[![Total Downloads](https://poser.pugx.org/codeception/codeception/downloads.png)](https://packagist.org/packages/codeception/codeception)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Codeception/Codeception/badges/quality-score.png?b=2.4)](https://scrutinizer-ci.com/g/Codeception/Codeception/?branch=2.4)

**Modern PHP Testing for everyone**

Codeception is a modern full-stack testing framework for PHP.
Inspired by BDD, it provides an absolutely new way of writing acceptance, functional and even unit tests.
Powered by PHPUnit.

| General |  Windows |  Webdriver  | HHVM |
| ------- | -------- | -------- | -------- |
| [![Build Status](https://secure.travis-ci.org/Codeception/Codeception.png?branch=2.4)](http://travis-ci.org/Codeception/Codeception) | [![Build status](https://ci.appveyor.com/api/projects/status/ntjj9i4y67d1rb7y?svg=true)](https://ci.appveyor.com/project/DavertMik/codeception/branch/2.4) | [![Build Status](https://semaphoreci.com/api/v1/codeception/codeception/branches/master/shields_badge.svg)](https://semaphoreci.com/codeception/codeception) | [![wercker status](https://app.wercker.com/status/b4eecd0596bedb65333ff7ab7836bc7f/s/ "wercker status")](https://app.wercker.com/project/byKey/b4eecd0596bedb65333ff7ab7836bc7f) |

#### Contributions

At Codeception we are glad to receive contributions from the community. If you want to send additions or fixes to the code or the documentation please check the [Contributing guide](https://github.com/Codeception/Codeception/blob/2.4/CONTRIBUTING.md).

### At a Glance

Describe what you test and how you test it. Use PHP to write descriptions faster.

Run tests and see what actions were taken and what results were seen.

#### Sample test

``` php
$I->wantTo('create wiki page');
$I->amOnPage('/');
$I->click('Pages');
$I->click('New');
$I->see('New Page');
$I->submitForm('form#new_page', ['title' => 'Movie Review']);
$I->see('page created'); // notice generated
$I->see('Movie Review','h1'); // head of page of is our title
$I->seeInCurrentUrl('pages/movie-review'); // slug is generated
$I->seeInDatabase('pages', ['title' => 'Movie Review']); // data is stored in database
```

For unit testing you can stay on classic PHPUnit tests, as Codeception can run them too.

## Installation

### Composer

```
php composer.phar require "codeception/codeception"
```

### Phar

Download [codecept.phar](http://codeception.com/codecept.phar)

Copy it into your project.

You can also make Codeception an executable and it put it into your `$PATH`, for instance:

```
wget http://codeception.com/codecept.phar

chmod +x codecept.phar

sudo mv codecept.phar /usr/local/bin/codecept

```

You can then run Codecept in the command line using: `codecept bootstrap`, `codecept run`, etc

Run CLI utility:

```
php codecept.phar
```

See also [Installation](http://codeception.com/install) | **[QuickStart](http://codeception.com/quickstart)**

## Getting Started

After you successfully installed Codeception, run this command:

```
codecept bootstrap
```

This will create a default directory structure and default test suites.

## Documentation

[Documentation](http://codeception.com/docs/01-Introduction)

Documentation is included within the project. Look for it in the ['docs' directory](https://github.com/Codeception/Codeception/tree/master/docs).

## License
MIT

(c) [Codeception Team](http://codeception.com/credits)
2011-2018
