Mink Zombie.js Driver
=====================

Usage Example
-------------

``` php
<?php

use Behat\Mink\Mink,
    Behat\Mink\Session,
    Behat\Mink\Driver\ZombieDriver,
    Behat\Mink\Driver\NodeJS\Server\ZombieServer;

$startUrl = 'http://example.com';

$mink = new Mink(array(
    'zombie' => new Session(new ZombieDriver(ZombieServer(
        $host, $port, $nodeBinary
    ))),
));

$mink->getSession('zombie')->getPage()->findLink('Chat')->click();
```

Installation
------------

``` json
{
    "requires": {
        "behat/mink":               "1.4.*",
        "behat/mink-zombie-driver": "*"
    }
}
```

``` bash
curl http://getcomposer.org/installer | php
php composer.phar install
```

Copyright
---------

Copyright (c) 2012 Pascal Cremer <b00gizm@gmail.com>

Maintainers
-----------

* Pascal Cremer [b00gizm](http://github.com/b00gizm)
