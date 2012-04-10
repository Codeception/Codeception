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


Attaches file stored in Codeception data directory to field specified.
Field is searched by its id|name|label|value or CSS selector.

 * param $field
 * param $filename


### checkOption


Check matched checkbox or radiobutton.
Field is searched by its id|name|label|value or CSS selector.

 * param $option


### click


Clicks on either link or button (for PHPBrowser) or on any selector for JS browsers.
Link text or css selector can be passed.

 * param $link


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


### fillField


Fill the field with given value.
Field is searched by its id|name|label|value or CSS selector.

 * param $field
 * param $value


### moveBack


Moves back in history


### moveForward


Moves forward in history


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

```

 * param $text
 * param null $selector


### seeCheckboxIsChecked


Asserts the checkbox is checked.
Field is searched by its id|name|label|value or CSS selector.

 * param $checkbox


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


### sendAjaxGetRequest


If your page triggers an ajax request, you can perform it manually.
This action sends a GET ajax request with specified params.

See ->sendAjaxPostRequest for examples.

 * param $uri
 * param $params


### sendAjaxPostRequest


If your page triggers an ajax request, you can perform it manually.
This action sends a POST ajax request with specified params.
Additional params can be passed as array.

Example:

Imagine that by clicking checkbox you trigger ajax request which updates user settings.
We emulate that click by running this ajax request manually.

``` php
<?php
$I->sendAjaxPostRequest('/updateSettings', array('notifications' => true); // POST
$I->sendAjaxGetRequest('/updateSettings', array('notifications' => true); // GET

```

 * param $uri
 * param $params


### submitForm


Submits a form located on page.
Specify the form by it's css or xpath selector.
Fill the form fields values as array.

Skipped fields will be filled by their values from page.
You don't need to click the 'Submit' button afterwards.
This command itself triggers the request to form's action.

Examples:

``` php
<?php
$I->submitForm('#login', array('login' => 'davert', 'password' => '123456'));

```

For sample Sign Up form:

``` html
<form action="/sign_up">
    Login: <input type="text" name="user[login]" /><br/>
    Password: <input type="password" name="user[password]" /><br/>
    Do you agree to out terms? <input type="checkbox" name="user[agree]" /><br/>
    Select pricing plan <select name="plan"><option value="1">Free</option><option value="2" selected="selected">Paid</option></select>
    <input type="submit" value="Submit" />
</form>
```
I can write this:

``` php
<?php
$I->submitForm('#userForm', array('user' => array('login' => 'Davert', 'password' => '123456', 'agree' => true)));

```
Note, that pricing plan will be set to Paid, as it's selected on page.

 * param $selector
 * param $params


### uncheckOption


Uncheck matched checkbox or radiobutton.
Field is searched by its id|name|label|value or CSS selector.

 * param $option
