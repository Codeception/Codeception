> # 🇺🇦 UKRAINE NEEDS YOUR HELP NOW!
>
> My name is Michael Bodnarchuk, I started Codeception in 2011. 
> I'm Ukrainian.
>
> Today, **my country, Ukraine, [is being invaded by the Russian Federation, right now](https://www.bbc.com/news/world-europe-60504334)**. I've fled Kyiv and now I'm safe with my family in the western part of Ukraine. At least for now.
> Russia is hitting target all over my country by ballistic missiles.
>
> **Please, save me and help to save my country!**
>
> Ukrainian National Bank opened [an account to Raise Funds for Ukraine’s Armed Forces](https://bank.gov.ua/en/news/all/natsionalniy-bank-vidkriv-spetsrahunok-dlya-zboru-koshtiv-na-potrebi-armiyi):
>
> ```
> SWIFT Code NBU: NBUA UA UX
> JP MORGAN CHASE BANK, New York
> SWIFT Code: CHASUS33
> Account: 400807238
> 383 Madison Avenue, New York, NY 10179, USA
> IBAN: UA843000010000000047330992708
> ```
>
> You can also donate to [charity supporting Ukrainian army](https://savelife.in.ua/en/donate/).
>
> **THANK YOU!**

# Codeception

[![Latest Stable](https://poser.pugx.org/Codeception/Codeception/version.png)](https://packagist.org/packages/Codeception/Codeception)
[![Total Downloads](https://poser.pugx.org/codeception/codeception/downloads.png)](https://packagist.org/packages/codeception/codeception)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Codeception/Codeception/badges/quality-score.png?b=4.0)](https://scrutinizer-ci.com/g/Codeception/Codeception/?branch=4.0)
[![StandWithUkraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/badges/StandWithUkraine.svg)](https://github.com/vshymanskyy/StandWithUkraine/blob/main/docs/README.md)

**Modern PHP Testing for everyone**

Codeception is a modern full-stack testing framework for PHP.
Inspired by BDD, it provides an absolutely new way of writing acceptance, functional and even unit tests.
Powered by PHPUnit.

| Build | Webdriver  |
| ----- | -------- |
| [![Build status](https://github.com/Codeception/Codeception/workflows/build/badge.svg)](https://github.com/Codeception/Codeception/actions?query=workflow%3Abuild) | [![Build Status](https://semaphoreci.com/api/v1/codeception/codeception/branches/3-0/shields_badge.svg)](https://semaphoreci.com/codeception/codeception) |

#### Contributions

At Codeception we are glad to receive contributions from the community. If you want to send additions or fixes to the code or the documentation please check the [Contributing guide](https://github.com/Codeception/Codeception/blob/4.0/CONTRIBUTING.md).

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

[View Documentation](https://codeception.com/docs/01-Introduction)

The documentation source files can be found at https://github.com/Codeception/codeception.github.com/tree/master/docs/.

## License
[MIT](https://github.com/Codeception/Codeception/blob/master/LICENSE)
