---
layout: page
title: Codeception - Documentation
---

## Selenium Module

Uses Mink to launch and manipulate Selenium Server (formerly the Selenium RC Server).

Note, all method takes CSS selectors to fetch elements.
For links, buttons, fields you can use names/values/ids of elements.
For form fields you can use name of matched label.

Will save a screenshot of browser window to log directory on fail.

### Installation

Take Selenium Server from http://seleniumhq.org/download

Execute it: java -jar selenium-server-standalone-x.xx.xxx.jar

Best used with Firefox browser.

Don't forget to turn on Db repopulation if you are using database.

### Configuration

* url *required* - start url for your app
* browser *required* - browser that would be launched
* host  - Selenium server host (localhost by default)
* port - Selenium server port (4444 by default)

### Public Properties

* session - contains Mink Session

### Actions


#### amOnPage


Opens the page.

 * param $page


#### attachFile

__not documented__


#### attachFileToField

__not documented__


#### blur

__not documented__


#### checkOption


Check matched checkbox or radiobutton.
 * param $option


#### click


Clicks on either link (for PHPBrowser) or on any selector for JS browsers.
Link text or css selector can be passed.

 * param $link


#### clickWithRightButton

__not documented__


#### dontSee

__not documented__


#### dontSeeCheckboxIsChecked

__not documented__


#### dontSeeInField

__not documented__


#### dontSeeLink

__not documented__


#### doubleClick

__not documented__


#### dragAndDrop


Drag first element to second

 * param $el1
 * param $el2


#### executeJs

__not documented__


#### fillField


Fill the field found by it's name with given value

 * param $field
 * param $value


#### fillFields


Shortcut for filling multiple fields by their names.
Array with field names => values expected.


 * param array $fields


#### focus

__not documented__


#### moveBack


Moves back in history


#### moveForward


Moves forward in history


#### moveMouseOver

__not documented__


#### press


Press the button, found by it's name.

 * param $button


#### pressKey

__not documented__


#### pressKeyDown

__not documented__


#### pressKeyUp

__not documented__


#### reloadPage


Reloads current page


#### see

__not documented__


#### seeCheckboxIsChecked

__not documented__


#### seeElement


Checks element visibility.
Fails if element exists but is invisible to user.

 * param $css


#### seeInCurrentUrl


Checks if current url contains the $uri.
 * param $uri


#### seeInField

__not documented__


#### seeLink

__not documented__


#### selectOption


Selects opition from selectbox.
Use CSS selector to match selectbox.
Either values or text of options can be used to fetch option.

 * param $select
 * param $option


#### uncheckOption


Uncheck matched checkbox or radiobutton.
 * param $option


#### wait

__not documented__


#### waitForJS

__not documented__
