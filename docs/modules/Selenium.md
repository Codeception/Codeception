# Selenium Module

Uses Mink to launch and manipulate Selenium Server (formerly the Selenium RC Server).

Note, all method takes CSS selectors to fetch elements.
For links, buttons, fields you can use names/values/ids of elements.
For form fields you can use name of matched label.

Will save a screenshot of browser window to log directory on fail.

## Installation

Take Selenium Server from http://seleniumhq.org/download

Execute it: java -jar selenium-server-standalone-x.xx.xxx.jar

Best used with Firefox browser.

Don't forget to turn on Db repopulation if you are using database.

## Configuration

* url *required* - start url for your app
* browser *required* - browser that would be launched
* host  - Selenium server host (localhost by default)
* port - Selenium server port (4444 by default)

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

__not documented__


### checkOption


Check matched checkbox or radiobutton.
Field is searched by its id|name|label|value or CSS selector.

 * param $option


### click


Clicks on either link or button (for PHPBrowser) or on any selector for JS browsers.
Link text or css selector can be passed.

 * param $link


### clickWithRightButton

__not documented__


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

__not documented__


### doubleClick

__not documented__


### dragAndDrop


Drag first element to second

 * param $el1
 * param $el2


### executeJs

__not documented__


### fillField


Fill the field with given value.
Field is searched by its id|name|label|value or CSS selector.

 * param $field
 * param $value


### focus

__not documented__


### moveBack


Moves back in history


### moveForward


Moves forward in history


### moveMouseOver

__not documented__


### pressKey

__not documented__


### pressKeyDown

__not documented__


### pressKeyUp

__not documented__


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

__not documented__


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

__not documented__


### waitForJS

__not documented__
