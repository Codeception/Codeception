# Selenium

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


### doubleClick

__not documented__

### clickWithRightButton

__not documented__

### moveMouseOver

__not documented__

### focus

__not documented__

### blur

__not documented__

### dragAndDrop


Drag first element to second

 * param $el1
 * param $el2

### seeElement


Checks element visibility.
Fails if element exists but is invisible to user.

 * param $css

### pressKey

__not documented__

### pressKeyUp

__not documented__

### pressKeyDown

__not documented__

### wait

__not documented__

### waitForJS

__not documented__

### executeJs

__not documented__

### amOnPage


Opens the page.

 * param $page

### dontSee


Check if current page doesn't contain the text specified.
Specify the css selector to match only specific region.

Examples:

```php
<?php
$I->dontSee('Login'); // I can suppose user is already logged in
$I->dontSee('Sign Up','h1'); // I can suppose it's not a signup page

```

 * param $text
 * param null $selector

### see


Check if current page contains the text specified.
Specify the css selector to match only specific region.

Examples:

``` php
<?php
$I->see('Logout'); // I can suppose user is logged in
$I->see('Sign Up','h1'); // I can suppose it's a signup page

```

 * param $text
 * param null $selector

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

### click


Clicks on either link (for PHPBrowser) or on any selector for JS browsers.
Link text or css selector can be passed.

 * param $link

### reloadPage


Reloads current page

### moveBack


Moves back in history

### moveForward


Moves forward in history

### fillField


Fill the field found by it's name with given value

 * param $field
 * param $value

### fillFields


Shortcut for filling multiple fields by their names.
Array with field names => values expected.


 * param array $fields

### press


Press the button, found by it's name.

 * param $button

### selectOption


Selects opition from selectbox.
Use CSS selector to match selectbox.
Either values or text of options can be used to fetch option.

 * param $select
 * param $option

### checkOption


Check matched checkbox or radiobutton.
 * param $option

### uncheckOption


Uncheck matched checkbox or radiobutton.
 * param $option

### attachFileToField

__not documented__

### seeInCurrentUrl


Checks if current url contains the $uri.
 * param $uri

### seeCheckboxIsChecked


Assert if the specified checkbox is checked.
Use css selector or xpath to match.

Example:

``` php
<?php
$I->seeCheckboxIsChecked('#agree'); // I suppose user agreed to terms
$I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user agreed to terms, If there is only one checkbox in form.

```

 * param $selector

### dontSeeCheckboxIsChecked


Assert if the specified checkbox is unchecked.
Use css selector or xpath to match.

Example:

``` php
<?php
$I->dontSeeCheckboxIsChecked('#agree'); // I suppose user didn't agree to terms
$I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user didn't check the first checkbox in form.

```

 * param $selector

### seeInField


Checks matched field has a passed value

 * param $field
 * param $value

### dontSeeInField


Checks matched field doesn't contain a value passed

 * param $field
 * param $value
