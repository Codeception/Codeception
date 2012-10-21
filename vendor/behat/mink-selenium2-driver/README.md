Mink Selenium2 (webdriver) Driver
=================================

- [![Build Status](https://secure.travis-ci.org/Behat/MinkSelenium2Driver.png?branch=master)](http://travis-ci.org/Behat/MinkSelenium2Driver)

Usage Example
-------------

``` php
<?php

use Behat\Mink\Mink,
    Behat\Mink\Session,
    Behat\Mink\Driver\Selenium2Driver;

use Selenium\Client as SeleniumClient;

$startUrl = 'http://example.com';

$mink = new Mink(array(
    'selenium2' => new Session(new Selenium2Driver($browser, null, $url)),
));

$mink->getSession('selenium2')->getPage()->findLink('Chat')->click();
```

Installation
------------

``` json
{
    "requires": {
        "behat/mink":                   "1.4.*",
        "behat/mink-selenium2-driver":  "*"
    }
}
```

``` bash
curl http://getcomposer.org/installer | php
php composer.phar install
```

Copyright
---------

Copyright (c) 2012 Pete Otaqui <pete@otaqui.com>.

Maintainers
-----------

* Pete Otaqui [pete-otaqui](http://github.com/pete-otaqui)
