# ZombieJS Module
**For additional reference,, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/ZombieJS)**
Uses Mink to manipulate Zombie.js headless browser (http://zombie.labnotes.org/)

Note, all methods take CSS selectors to fetch elements.
For links, buttons, fields you can use names/values/ids of elements.
For form fields you can use input[name=fieldname] notation.

## Status

* Maintainer: **synchrone**
* stability: stable
* Contact: https://github.com/synchrone
* relies on [Mink](http://mink.behat.org)


## Installation

In order to talk with zombie.js server, you should install and configure zombie.js first:

* Install node.js by following instructions from the official site: http://nodejs.org/.
* Install npm (node package manager) by following instructions from the http://npmjs.org/.
* Install zombie.js with npm:
``` $ npm install -g zombie@0.13.0 ```
Note: Behat/Mink states that there are compatibility issues with zombie > 0.13, and their manual
says to install version 0.12.15, BUT it has some bugs, so you'd rather install 0.13

After installing npm and zombie.js, you’ll need to add npm libs to your **NODE_PATH**. The easiest way to do this is to add:

``` export NODE_PATH="/PATH/TO/NPM/node_modules" ```
into your **.bashrc**.

Also note that this module requires php5-http PECL extension to parse returned headers properly

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


Attaches file from Codeception data directory to upload field.

Example:

``` php
<?php
// file is stored in 'tests/data/tests.xls'
$I->attachFile('prices.xls');
?>
```

 * param $field
 * param $filename


### blur


Removes focus from link or button or any node found by CSS or XPath
XPath or CSS selectors are accepted.

 * param $el


### checkOption


Ticks a checkbox.
For radio buttons use `selectOption` method.

Example:

``` php
<?php
$I->checkOption('#agree');
?>
```

 * param $option


### click


Perform a click on link or button.
Link or button are found by their names or CSS selector.
Submits a form if button is a submit type.

If link is an image it's found by alt attribute value of image.
If button is image button is found by it's value
If link or button can't be found by name they are searched by CSS selector.

Examples:

``` php
<?php
// simple link
$I->click('Logout');
// button of form
$I->click('Submit');
// CSS button
$I->click('#form input[type=submit]');
// XPath
$I->click('//form/*[@type=submit]')
?>
```
 * param $link


### clickWithRightButton


Clicks with right button on link or button or any node found by CSS or XPath

 * param $link


### dontSee


Check if current page doesn't contain the text specified.
Specify the css selector to match only specific region.

Examples:

```php
<?php
$I->dontSee('Login'); // I can suppose user is already logged in
$I->dontSee('Sign Up','h1'); // I can suppose it's not a signup page
$I->dontSee('Sign Up','//body/h1'); // with XPath
```

 * param $text
 * param null $selector


### dontSeeCheckboxIsChecked


Assert if the specified checkbox is unchecked.
Use css selector or xpath to match.

Example:

``` php
<?php
$I->dontSeeCheckboxIsChecked('#agree'); // I suppose user didn't agree to terms
$I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user didn't check the first checkbox in form.

```

 * param $checkbox


### dontSeeInField


Checks that an input field or textarea doesn't contain value.

Example:

``` php
<?php
$I->dontSeeInField('form textarea[name=body]','Type your comment here');
$I->dontSeeInField('form input[type=hidden]','hidden_value');
$I->dontSeeInField('#searchform input','Search');
$I->dontSeeInField('//form/*[@name=search]','Search');
?>
```

 * param $field
 * param $value


### dontSeeLink


Checks if page doesn't contain the link with text specified.
Specify url to narrow the results.

Examples:

``` php
<?php
$I->dontSeeLink('Logout'); // I suppose user is not logged in

```

 * param $text
 * param null $url


### doubleClick


Double clicks on link or button or any node found by CSS or XPath

 * param $link


### dragAndDrop


Drag first element to second
XPath or CSS selectors are accepted.

 * param $el1
 * param $el2


### executeJs


Executes any JS code.

 * param $jsCode


### fillField


Fills a text field or textarea with value.

 * param $field
 * param $value


### focus


Moves focus to link or button or any node found by CSS or XPath

 * param $el


### grabAttribute

__not documented__


### grabTextFrom


Finds and returns text contents of element.
Element is searched by CSS selector, XPath or matcher by regex.

Example:

``` php
<?php
$heading = $I->grabTextFrom('h1');
$heading = $I->grabTextFrom('descendant-or-self::h1');
$value = $I->grabTextFrom('~<input value=(.*?)]~sgi');
?>
```

 * param $cssOrXPathOrRegex
 * return mixed


### grabValueFrom


Finds and returns field and returns it's value.
Searches by field name, then by CSS, then by XPath

Example:

``` php
<?php
$name = $I->grabValueFrom('Name');
$name = $I->grabValueFrom('input[name=username]');
$name = $I->grabValueFrom('descendant-or-self::form/descendant::input[@name = 'username']');
?>
```

 * param $field
 * return mixed


### headRequest


 * param string $url The URL to make HEAD request to
 * return array Header-Name => Value array


### moveBack


Moves back in history


### moveForward


Moves forward in history


### moveMouseOver


Moves mouse over link or button or any node found by CSS or XPath

 * param $link


### pressKey


Presses key on element found by css, xpath is focused
A char and modifier (ctrl, alt, shift, meta) can be provided.

Example:

``` php
<?php
$I->pressKey('#page','u');
$I->pressKey('#page','u','ctrl');
$I->pressKey('descendant-or-self::*[@id='page']','u');
?>
```

 * param $element
 * param $char char can be either char ('b') or char-code (98)
 * param null $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')


### pressKeyDown


Presses key down on element found by CSS or XPath.

For example see 'pressKey'.

 * param $element
 * param $char char can be either char ('b') or char-code (98)
 * param null $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')


### pressKeyUp


Presses key up on element found by CSS or XPath.

For example see 'pressKey'.

 * param $element
 * param $char char can be either char ('b') or char-code (98)
 * param null $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')


### reloadPage


Reloads current page


### see


Check if current page contains the text specified.
Specify the css selector to match only specific region.

Examples:

``` php
<?php
$I->see('Logout'); // I can suppose user is logged in
$I->see('Sign Up','h1'); // I can suppose it's a signup page
$I->see('Sign Up','//body/h1'); // with XPath

```

 * param $text
 * param null $selector


### seeCheckboxIsChecked


Assert if the specified checkbox is checked.
Use css selector or xpath to match.

Example:

``` php
<?php
$I->seeCheckboxIsChecked('#agree'); // I suppose user agreed to terms
$I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user agreed to terms, If there is only one checkbox in form.
$I->seeCheckboxIsChecked('//form/input[@type=checkbox and  * name=agree]');

```

 * param $checkbox


### seeElement


Checks element visibility.
Fails if element exists but is invisible to user.
Eiter CSS or XPath can be used.

 * param $selector


### seeInCurrentUrl


Checks that current uri contains value

 * param $uri


### seeInField


Checks that an input field or textarea contains value.

Example:

``` php
<?php
$I->seeInField('form textarea[name=body]','Type your comment here');
$I->seeInField('form input[type=hidden]','hidden_value');
$I->seeInField('#searchform input','Search');
$I->seeInField('//form/*[@name=search]','Search');
?>
```

 * param $field
 * param $value


### seeLink


Checks if there is a link with text specified.
Specify url to match link with exact this url.

Examples:

``` php
<?php
$I->seeLink('Logout'); // matches <a href="#">Logout</a>
$I->seeLink('Logout','/logout'); // matches <a href="/logout">Logout</a>

```

 * param $text
 * param null $url


### selectOption


Selects an option in select tag or in radio button group.

Example:

``` php
<?php
$I->selectOption('form select[name=account]', 'Premium');
$I->selectOption('form input[name=payment]', 'Monthly');
$I->selectOption('//form/select[@name=account]', 'Monthly');
?>
```

 * param $select
 * param $option


### uncheckOption


Unticks a checkbox.

Example:

``` php
<?php
$I->uncheckOption('#notify');
?>
```

 * param $option


### wait


Wait for x miliseconds

 * param $miliseconds


### waitForJS


Waits for x miliseconds or until JS condition turns true.

 * param $miliseconds
 * param $jsCondition
