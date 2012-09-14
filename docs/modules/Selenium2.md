# Selenium2 Module

Uses Mink to manipulate Selenium2 WebDriver

Note that all method take CSS selectors to fetch elements.

On test failure the browser window screenshot will be saved to log directory

## Installation

Download Selenium2 [WebDriver](http://code.google.com/p/selenium/downloads/list?q=selenium-server-standalone-2)
Launch the daemon: `java -jar selenium-server-standalone-2.xx.xxx.jar`

Don't forget to turn on Db repopulation if you are using database.

## Configuration

* url *required* - start url for your app
* browser *required* - browser that would be launched
* host  - Selenium server host (localhost by default)
* port - Selenium server port (4444 by default)
* delay - set delay between actions in milliseconds (1/1000 of second) if they run too fast

## Public Properties

* session - contains Mink Session

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


Attaches file stored in Codeception data directory to field specified.
Field is searched by its id|name|label|value or CSS selector.

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


Check matched checkbox or radiobutton.
Field is searched by its id|name|label|value or CSS selector.

 * param $option


### click


Clicks on either link or button (for PHPBrowser) or on any selector for JS browsers.
Link text or css selector can be passed.

 * param $link


### clickWithRightButton


Clicks with right button on link or button or any node found by CSS or XPath

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


Checks if the document hasn't link that contains specified
text (or text and url)

 * param  string $text
 * param  string $url (Default: null)
 * return mixed


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


Fill the field with given value.
Field is searched by its id|name|label|value or CSS selector.

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

__not documented__


### seeCheckboxIsChecked


Asserts the checkbox is checked.
Field is searched by its id|name|label|value or CSS selector.

 * param $checkbox


### seeElement


Checks element visibility.
Fails if element exists but is invisible to user.
Eiter CSS or XPath can be used.

 * param $selector


### seeInCurrentUrl


Checks if current url contains the $uri.

 * param $uri


### seeInField


Checks the value of field is equal to value passed.

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
