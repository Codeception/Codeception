<?php
namespace Codeception\Module;

use Phalcon\Di;
use Phalcon\Mvc\Model as PhalconModel;
use Codeception\TestCase;

/**
 * This module provides integration with [Phalcon framework](http://www.phalconphp.com/) (2.x).
 * Please try it and leave your feedback.
 * The module is based on the Phalcon1 module.
 *
 * ## Demo Project
 *
 * <https://github.com/phalcon/forum>
 *
 * ## Status
 *
 * * Maintainer: **Serghei Iakovlev**
 * * Stability: **stable**
 * * Contact: sadhooklay@gmail.com
 *
 * ## Example
 *
 *     modules:
 *         enabled:
 *             - Phalcon2:
 *                 bootstrap: 'app/config/bootstrap.php'
 *                 cleanup: true
 *                 savepoints: true
 *
 * ## Config
 *
 * The following configurations are required for this module:
 * * boostrap: the path of the application bootstrap file</li>
 * * cleanup: cleanup database (using transactions)</li>
 * * savepoints: use savepoints to emulate nested transactions</li>
 *
 * The application bootstrap file must return Application object but not call its handle() method.
 *
 * Sample bootstrap (`app/config/bootstrap.php`):
 *
 * ``` php
 * <?php
 * $config = include __DIR__ . "/config.php";
 * include __DIR__ . "/loader.php";
 * $di = new \Phalcon\DI\FactoryDefault();
 * include __DIR__ . "/services.php";
 * return new \Phalcon\Mvc\Application($di);
 * ?>
 * ```
 *
 * ## API
 *
 * * di - `Phalcon\Di\Injectable` instance
 * * client - `BrowserKit` client
 *
 * ## Parts
 *
 * * ORM - include only haveRecord/grabRecord/seeRecord/dontSeeRecord actions
 *
 */
class Phalcon2 extends Phalcon1
{
}
