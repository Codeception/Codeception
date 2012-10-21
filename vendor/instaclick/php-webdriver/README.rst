============================
PHP WebDriver for Selenium 2
============================

This fork is based on Facebook's php-webdriver project. [1]_

Distinguishing features of this fork:

* Up-to-date with Selenium 2 JSON Wire Protocol [2]_ (including WebDriver commands yet to be documented)
* *master* branch where class names and file organization follows PSR-0 conventions for php 5.3+ namespaces
* coding style follows Symfony2 coding standard
* auto-generate API documentation via phpDocumentor 2.x [3]_
* includes a basic web test runner

The *5.2.x* branch features class names and file re-organization that follows PEAR/ZF1 conventions.  However,
bug fixes and enhancements from the master branch may not be backported.

Downloads
=========

* Packagist (dev-master) http://packagist.org/packages/instaclick/php-webdriver
* Github https://github.com/instaclick/php-webdriver

Notes
=====

.. [1] https://github.com/facebook/php-webdriver/
.. [2] http://code.google.com/p/selenium/wiki/JsonWireProtocol
.. [3] http://phpdoc.org/
