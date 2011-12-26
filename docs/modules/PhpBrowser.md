# PhpBrowser

Uses Mink (http://mink.behat.org) with Goutte Driver to interact with your application.
Contains all Mink actions and additional ones, listed below.

Use to perform web acceptance tests with non-javascript browser.

If test fails stores last shown page in 'output' dir.

## Configuration

* url *required* - start url of your app

## Public Properties

* session - contains Mink Session


## Actions


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
/
### sendAjaxGetRequest


If your page triggers an ajax request, you can perform it manually.
This action sends a GET ajax request with specified params.

See ->sendAjaxPostRequest for examples.

 * param $uri
 * param $params

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
