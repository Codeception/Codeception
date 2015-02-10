# Symfony1 Module

**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/2.0/src/Codeception/Module/Symfony1.php)**


Module that interacts with Symfony 1.4 applications.

Replaces functional testing framework from symfony. Authorization features uses Doctrine and sfDoctrineGuardPlugin.
Uses native symfony connections and test classes. Provides additional informations on every actions.

If test fails stores last shown page in 'log' dir.

Please note, this module doesn't implement standard frameworks interface.

## Status

* Maintainer: **davert**
* Stability: **stable**
* Contact: codecept@davert.mail.ua

## Configuration

* app *required* - application you want to test. In most cases it will be 'frontend'

## Public Properties
* browser - current instance of sfBrowser class.



### amLoggedAs
 
Log in as sfDoctrineGuardUser.
Only name of user should be provided.
Fetches user by it's username from sfGuardUser table.

 * `param` $name
 \Exception


### amOnPage
 
Opens the page.

 * `param` $page


### click
 
Click on link or button and move to next page.
Either link text, css selector, or xpath can be passed

 * `param` $link


### clickSubmitButton
 
Emulates click on form's submit button.
You don't need that action if you fill form by ->submitForm action.

 * `param` $selector


### dontSee
 
Check if current page doesn't contain the text specified.
Specify the css selector to match only specific region.

Examples:

```php
<?php
$I->dontSee('Login'); // I can suppose user is already logged in
$I->dontSee('Sign Up','h1'); // I can suppose it's not a signup page

```

 * `param` $text
 * `param null` $selector


### dontSeeCheckboxIsChecked
 
Assert if the specified checkbox is unchecked.
Use css selector or xpath to match.

Example:

``` php
<?php
$I->dontSeeCheckboxIsChecked('#agree'); // I suppose user didn't agree to terms
$I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user didn't check the first checkbox in form.

```

 * `param` $selector


### dontSeeLink
 
Checks if page doesn't contain the link with text specified.
Specify url to narrow the results.

Examples:

``` php
<?php
$I->dontSeeLink('Logout'); // I suppose user is not logged in

```

 * `param` $text
 * `param null` $url


### see
 
Check if current page contains the text specified.
Specify the css selector to match only specific region.

Examples:

``` php
<?php
$I->see('Logout'); // I can suppose user is logged in
$I->see('Sign Up','h1'); // I can suppose it's a signup page

```

 * `param` $text
 * `param null` $selector


### seeCheckboxIsChecked
 
Assert if the specified checkbox is checked.
Use css selector or xpath to match.

Example:

``` php
<?php
$I->seeCheckboxIsChecked('#agree'); // I suppose user agreed to terms
$I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user agreed to terms, If there is only one checkbox in form.

```

 * `param` $selector


### seeEmailReceived
 
Checks if there were at least one email sent through Symfony test mailer.


### seeErrorInField
 
Checks for invalid value in Symfony1 form.
Matches the first sfForm instance from controller and returns getErrorSchema() values.
Specify field which should contain error message.

 * `param` $field


### seeErrorsInForm
 
Performs validation of Symfony1 form.
Matches the first sfForm instance from controller and returns getErrorSchema() values.
Shows all errors in debug.


### seeFormIsValid
 
Performs validation of Symfony1 form.
Matches the first sfForm instance from controller and returns isValid() value.


### seeLink
 
Checks if there is a link with text specified.
Specify url to match link with exact this url.

Examples:

``` php
<?php
$I->seeLink('Logout'); // matches <a href="#">Logout</a>
$I->seeLink('Logout','/logout'); // matches <a href="/logout">Logout</a>

```

 * `param` $text
 * `param null` $url


### sendAjaxGetRequest
 
If your page triggers an ajax request, you can perform it manually.
This action sends a GET ajax request with specified params.

See ->sendAjaxPostRequest for examples.

 * `param` $uri
 * `param` $params


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

 * `param` $uri
 * `param` $params


### signIn
 
Sign's user in with sfGuardAuth.
Uses standard path: /sfGuardAuth/signin for authorization.
Provide username and password.

 * `param` $username
 * `param` $password


### signOut
 
Sign out is performing by triggering '/logout' url.



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

 * `param` $selector
 * `param` $params

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.0/src/Codeception/Module/Symfony1.php">Help us to improve documentation. Edit module reference</a></div>
