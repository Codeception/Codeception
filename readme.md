[![Stand With Ukraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner-direct.svg)](https://stand-with-ukraine.pp.ua)

# Codeception

[![Latest Stable](https://poser.pugx.org/Codeception/Codeception/version.png)](https://packagist.org/packages/Codeception/Codeception)
[![Total Downloads](https://poser.pugx.org/codeception/codeception/downloads.png)](https://packagist.org/packages/codeception/codeception)
[![StandWithUkraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/badges/StandWithUkraine.svg)](https://github.com/vshymanskyy/StandWithUkraine/blob/main/docs/README.md)
[![Build status](https://github.com/Codeception/Codeception/workflows/build/badge.svg)](https://github.com/Codeception/Codeception/actions?query=workflow%3Abuild)

**Modern PHP Testing for everyone**

Codeception is a modern full-stack testing framework for PHP.
Inspired by BDD, it provides an absolutely new way of writing acceptance, functional and even unit tests.

#### Contributions

At Codeception we are glad to receive contributions from the community. If you want to send additions or fixes to the code or the documentation please check the [Contributing guide](https://github.com/Codeception/Codeception/blob/5.0/CONTRIBUTING.md).

### At a Glance

Describe what you test and how you test it. Use PHP to write descriptions faster.

Run tests and see what actions were taken and what results were seen.

#### Sample test

``` php
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

TODO: Document how to install the modules, e.g.
```
php composer.phar require "codeception/module-phpbrowser"
```

### Phar

Download [codecept.phar](https://codeception.com/codecept.phar)

Copy it into your project.

You can also make Codeception an executable and it put it into your `$PATH`, for instance:

```
wget https://codeception.com/codecept.phar

chmod +x codecept.phar

sudo mv codecept.phar /usr/local/bin/codecept

```

You can then run Codecept in the command line using: `codecept bootstrap`, `codecept run`, etc

Run CLI utility:

```
php codecept.phar
```

See also [Installation](https://codeception.com/install) | **[QuickStart](https://codeception.com/quickstart)**

## Getting Started

After you successfully installed Codeception, run this command:

```
codecept bootstrap
```

This will create a default directory structure and default test suites.

## Documentation

[View Documentation](https://codeception.com/docs/Introduction)

The documentation source files can be found at https://github.com/Codeception/codeception.github.com/tree/master/docs/.

## License
[MIT](https://github.com/Codeception/Codeception/blob/master/LICENSE)
