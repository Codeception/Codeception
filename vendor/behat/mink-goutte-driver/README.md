Mink Goutte Driver
==================

Usage Example
-------------

``` php
<?php

use Behat\Mink\Mink,
    Behat\Mink\Session,
    Behat\Mink\Driver\GoutteDriver,
    Behat\Mink\Driver\Goutte\Client as GoutteClient;

$startUrl = 'http://example.com';

$mink = new Mink(array(
    'goutte' => new Session(new GoutteDriver(GoutteClient($startUrl))),
));

$mink->getSession('goutte')->getPage()->findLink('Chat')->click();
```

Installation
------------

``` json
{
    "requires": {
        "behat/mink":               "1.4.*",
        "behat/mink-goutte-driver": "*"
    }
}
```

``` bash
curl http://getcomposer.org/installer | php
php composer.phar install
```

Copyright
---------

Copyright (c) 2012 Konstantin Kudryashov (ever.zet). See LICENSE for details.

Maintainers
-----------

* Konstantin Kudryashov [everzet](http://github.com/everzet)
