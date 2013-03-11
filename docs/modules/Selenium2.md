# Selenium2 Module
**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Selenium2.php)**


Uses Mink to manipulate Selenium2 WebDriver

Note that all method take CSS selectors to fetch elements.

On test failure the browser window screenshot will be saved to log directory

## Installation

Download [Selenium2 WebDriver](http://code.google.com/p/selenium/downloads/list?q=selenium-server-standalone-2)
Launch the daemon: `java -jar selenium-server-standalone-2.xx.xxx.jar`

Don't forget to turn on Db repopulation if you are using database.

## Status

* Maintainer: **davert**
* Stability: **stable**
* Contact: codecept@davert.mail.ua
* relies on [Mink](http://mink.behat.org)

## Configuration

* url *required* - start url for your app
* browser *required* - browser that would be launched
* host  - Selenium server host (localhost by default)
* port - Selenium server port (4444 by default)
* delay - set delay between actions in milliseconds (1/1000 of second) if they run too fast
* capabilities - sets Selenium2 [desired capabilities](http://code.google.com/p/selenium/wiki/DesiredCapabilities). Should be a key-value array.

### Example (`acceptance.suite.yml`)

    modules: 
       enabled: [Selenium2]
       config:
          Selenium2:
             url: 'http://localhost/' 
             browser: firefox
             capabilities:
                 unexpectedAlertBehaviour: 'accept'

## Public Properties

* session - contains Mink Session
* webDriverSession - contains webDriverSession object, i.e. $session from [php-webdriver](https://github.com/facebook/php-webdriver)

## Actions


### acceptPopup


Accept alert or confirm popup

Example:
``` php
<?php
$I->click('Show alert popup');
$I->acceptPopup();

```


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


### cancelPopup


Dismiss alert or confirm popup

Example:
``` php
<?php
$I->click('Show confirm popup');
$I->cancelPopup();

```


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


Clicks on either link or button (for PHPBrowser) or on any selector for JS browsers.
Link text or css selector can be passed.

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
Field is matched either by label or CSS or Xpath
Example:

``` php
<?php
$I->dontSeeInField('Body','Type your comment here');
$I->dontSeeInField('form textarea[name=body]','Type your comment here');
$I->dontSeeInField('form input[type=hidden]','hidden_value');
$I->dontSeeInField('#searchform input','Search');
$I->dontSeeInField('//form/*[@name=search]','Search');
?>
```

 * param $field
 * param $value


### dontSeeInPopup


Check if popup don't contains the $text

Example:
``` php
<?php
$I->click();
$I->dontSeeInPopup('Error message');

```

 * param string $text


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
Field is matched either by label or CSS or Xpath

Example:

``` php
<?php
$I->seeInField('Body','Type your comment here');
$I->seeInField('form textarea[name=body]','Type your comment here');
$I->seeInField('form input[type=hidden]','hidden_value');
$I->seeInField('#searchform input','Search');
$I->seeInField('//form/*[@name=search]','Search');
?>
```

 * param $field
 * param $value


### seeInPopup


Checks if popup contains the $text

Example:
``` php
<?php
$I->click('Show alert popup');
$I->seeInPopup('Error message');

```

 * param string $text


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


### switchToIFrame


Switch to another frame

Example:
``` html
<iframe name="another_frame" src="http://example.com">

```

``` php
<?php
# switch to iframe
$I->switchToIFrame("another_frame");
# switch to parent page
$I->switchToIFrame();

```

 * param string|null $name


### switchToWindow


Switch to another window

Example:
``` html
<input type="button" value="Open window" onclick="window.open('http://example.com', 'another_window')">

```

``` php
<?php
$I->click("Open window");
# switch to another window
$I->switchToWindow("another_window");
# switch to parent window
$I->switchToWindow();

```

 * param string|null $name


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


Wait for x milliseconds

 * param $milliseconds


### waitForJS


Waits for x milliseconds or until JS condition turns true.

 * param $milliseconds
 * param $jsCondition
