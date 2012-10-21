PHP Selenium library
====================

Selenium is a tool for web test automation. This library is a client of the
Selenium Server for PHP.

It provides a fluid interface with autocompletion and documentation in modern IDEs.

::

   <?php
     // ...
     $browser
         ->open('/')
         ->click(Selenium\Locator::linkContaining('Blog'))
         ->waitForPageToLoad()
     ;

     echo $browser->getTitle();


Requirements
::::::::::::

To use this library you will need :

* PHP 5.3
* A Selenium Server


Dependencies
::::::::::::

2 dependencies are present in sub-modules :

* Symfony2 DomCrawler  : used for generating the Browser class
* Symfony2 ClassLoader : used in ``autoload.php``

It's up to you to decide how to use it.


How to include it ?
:::::::::::::::::::

To make it work, you just need to add the classes to your autoloader.

If you have no existing autoloader, include ``autoload.php``


How to use it ?
:::::::::::::::

Take a look at examples in ``test/`` folder.


References
::::::::::

* Selenium Download Page : http://seleniumhq.org/download/
* Selenium Core Reference : http://release.seleniumhq.org/selenium-core/1.0/reference.html
* Client Driver Protocol  : http://wiki.openqa.org/display/SRC/Specifications+for+Selenium+Remote+Control+Client+Driver+Protocol
