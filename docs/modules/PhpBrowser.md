# PhpBrowser

Uses Mink (http://mink.behat.org) with Goutte Driver to interact with your application.
Contains all Mink actions and additional ones, listed below.

Use to perform web acceptance tests with non-javascript browser.

If test fails stores last shown page in 'output' dir.

## Configuration

* start *required* - the url of your app
* output *required* - dir were last shown page should be stored on fail.



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

__not documented__

### dontSee

__not documented__

### see

__not documented__

### click

__not documented__

### reloadPage

__not documented__

### moveBack

__not documented__

### moveForward

__not documented__

### fillField

__not documented__

### fillFields

__not documented__

### selectOption

__not documented__

### checkOption

__not documented__

### uncheckOption

__not documented__

### attachFileToField

__not documented__

### seeInCurrentAddress

__not documented__

### seeCheckboxIsChecked

__not documented__

### dontSeeCheckboxIsChecked

__not documented__

### seeInField

__not documented__

### dontSeeInField

__not documented__

### getModule

__not documented__
