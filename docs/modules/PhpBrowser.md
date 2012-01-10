# PhpBrowser Module

Uses Mink (http://mink.behat.org) with Goutte Driver to interact with your application.
Contains all Mink actions and additional ones, listed below.

Use to perform web acceptance tests with non-javascript browser.

If test fails stores last shown page in 'output' dir.

## Configuration

* url *required* - start url of your app

## Public Properties

* session - contains Mink Session


## Actions


### amOnPage


Opens the page.

 * param $page


### attachFile

__not documented__


### attachFileToField

__not documented__


### checkOption


Check matched checkbox or radiobutton.
 * param $option


### click


Clicks on either link (for PHPBrowser) or on any selector for JS browsers.
Link text or css selector can be passed.

 * param $link


### dontSee

__not documented__


### dontSeeCheckboxIsChecked

__not documented__


### dontSeeInField

__not documented__


### dontSeeLink

__not documented__


### fillField


Fill the field found by it's name with given value

 * param $field
 * param $value


### fillFields


Shortcut for filling multiple fields by their names.
Array with field names => values expected.


 * param array $fields


### moveBack


Moves back in history


### moveForward


Moves forward in history


### press


Press the button, found by it's name.

 * param $button


### reloadPage


Reloads current page


### see

__not documented__


### seeCheckboxIsChecked

__not documented__


### seeInCurrentUrl


Checks if current url contains the $uri.
 * param $uri


### seeInField

__not documented__


### seeLink

__not documented__


### selectOption


Selects opition from selectbox.
Use CSS selector to match selectbox.
Either values or text of options can be used to fetch option.

 * param $select
 * param $option


### sendAjaxGetRequest

__not documented__


### sendAjaxPostRequest

__not documented__


### submitForm

__not documented__


### uncheckOption


Uncheck matched checkbox or radiobutton.
 * param $option
