# WebDebug Module

# Web Debugging Module

This is a mini-module with helper actions to debug acceptance tests.
Use it with Selenium, Selenium2, ZombieJS, or PhpBrowser module.
Whenever none of this modules are connected the exception is thrown.

## Configuration:

* disable: false (optional) - stop making dumps and screenshots. Useful when you don't need debug anymore but you don't wanna change the code of your tests.

## Features

* save screenshots of current page
* save html (xml, json) code of current page
* more to come...

## Example configuration

``` yaml

class_name: WebGuy
modules:
     enabled:
         - Selenium
         - WebDebug # <-- this module
         - WebHelper
         - Db 
     config:
         Selenium:
             url: http://web.tenderway
             browser: firefox
```


## Actions


### makeAResponseDump


Saves current response content to _logs/debug/
By default a response is treated as HTML, so all stored files will have html extension

Optionally you can provide a dump name.

 * param $name


### makeAScreenshot


Saves screenshot of browser to _logs/debug/
Optionally you can provide a screenshot name.

 * param $name
