# ZombieJS Module

* Uses Mink to manipulate Zombie.js headless browser (http://zombie.labnotes.org/)
*
* Note, all methods take CSS selectors to fetch elements.
* For links, buttons, fields you can use names/values/ids of elements.
* For form fields you can use input[name=fieldname] notation.
*
* ## Installation
*
In order to talk with zombie.js server, you should install and configure zombie.js first:

* Install node.js by following instructions from the official site: http://nodejs.org/.
* Install npm (node package manager) by following instructions from the http://npmjs.org/.
* Install zombie.js with npm:
``` $ npm install -g zombie ```
After installing npm and zombie.js, you’ll need to add npm libs to your **NODE_PATH**. The easiest way to do this is to add:

``` export NODE_PATH="/PATH/TO/NPM/node_modules" ```
into your **.bashrc**.

Also not that this module requires php5-http PECL extension to parse returned headers properly

Don't forget to turn on Db repopulation if you are using database.

## Configuration

* host - simply defines the host on which zombie.js will be started. It’s **127.0.0.1** by default.
* port - defines a zombie.js port. Default one is **8124**.
* node_bin - defines full path to node.js binary. Default one is just **node**
* script - defines a node.js script to start zombie.js server. If you pass a **null** the default script will be used. Use this option carefully!
* threshold - amount of milliseconds (1/1000 of second) for the process to wait  (as of \Behat\Mink\Driver\Zombie\Server)
* autostart - whether zombie.js should be started automatically. Defaults to **true**

## Public Properties

* session - contains Mink Session

## Actions


### amOnPage


Opens the page.

 * param $page


### attachFile


Attaches file stored in Codeception data directory to field specified.
Field is searched by its id|name|label|value or CSS selector.

 * param $field
 * param $filename


### blur


Removes focus from link or button or any node found by css

 * param $el


### checkOption


Check matched checkbox or radiobutton.
Field is searched by its id|name|label|value or CSS selector.

 * param $option


### click


Clicks on either link or button (for PHPBrowser) or on any selector for JS browsers.
Link text or css selector can be passed.

 * param $link


### clickWithRightButton


Clicks with right button on link or button or any node found by css

 * param $link


### dontSee

__not documented__


### dontSeeCheckboxIsChecked


Asserts that checbox is not checked
Field is searched by its id|name|label|value or CSS selector.

 * param $checkbox


### dontSeeInField


Checks the value in field is not equal to value passed.
Field is searched by its id|name|label|value or CSS selector.

 * param $field
 * param $value


### dontSeeLink


Checks if the document hasn't link that contains specified
text (or text and url)

 * param  string $text
 * param  string $url (Default: null)
 * return mixed


### doubleClick


Double clicks on link or button or any node found by css

 * param $link


### dragAndDrop


Drag first element to second

 * param $el1
 * param $el2


### executeJs


Executes any JS code.

 * param $jsCode


### fillField


Fill the field with given value.
Field is searched by its id|name|label|value or CSS selector.

 * param $field
 * param $value


### focus


Moves focus to link or button or any node found by css

 * param $el


### headRequest


 * param string $url The URL to make HEAD request to
 * return array Header-Name => Value array


### moveBack


Moves back in history


### moveForward


Moves forward in history


### moveMouseOver


Moves mouse over link or button or any node found by css

 * param $link


### pressKey


Presses key on element found by css is focused
A char and modifier (ctrl, alt, shift, meta) can be provided.

Example:

``` php
<?php
$I->pressKey('#page','u','ctrl');
?>
```

 * param $element
 * param $char char can be either char ('b') or char-code (98)
 * param null $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')


### pressKeyDown


Presses key down on element found by CSS.

For example see 'pressKey'.

 * param $element
 * param $char char can be either char ('b') or char-code (98)
 * param null $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')


### pressKeyUp


Presses key up on element found by CSS.

For example see 'pressKey'.

 * param $element
 * param $char char can be either char ('b') or char-code (98)
 * param null $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')


### reloadPage


Reloads current page


### see

__not documented__


### seeCheckboxIsChecked


Asserts the checkbox is checked.
Field is searched by its id|name|label|value or CSS selector.

 * param $checkbox


### seeElement


Checks element visibility.
Fails if element exists but is invisible to user.

 * param $css


### seeInCurrentUrl


Checks if current url contains the $uri.

 * param $uri


### seeInField


Checks the value of field is equal to value passed.

 * param $field
 * param $value


### seeLink


Checks if the document has link that contains specified
text (or text and url)

 * param  string $text
 * param  string $url (Default: null)
 * return mixed


### selectOption


Selects opition from selectbox.
Use field name|label|value|id or CSS selector to match selectbox.
Either values or text of options can be used to fetch option.

 * param $select
 * param $option


### uncheckOption


Uncheck matched checkbox or radiobutton.
Field is searched by its id|name|label|value or CSS selector.

 * param $option


### wait


Wait for x miliseconds

 * param $miliseconds


### waitForJS


Waits for x miliseconds or until JS condition turns true.

 * param $miliseconds
 * param $jsCondition
