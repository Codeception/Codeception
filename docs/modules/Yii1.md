# Yii1 Module
**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Yii1.php)**


This module provides integration with Yii framework (http://www.yiiframework.com/) (1.1.14dev).

The following configurations are available for this module:
<ul>
<li>appPath - full path to the application, include index.php</li>
<li>url - full url to the index.php entry script</li>
</ul>
In your index.php you must return array with correct configuration for the application:

For the simple created yii application index.php will be like this:
<pre>
// change the following paths if necessary
$yii=dirname(__FILE__).'/../yii/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);
require_once($yii);
return array(
	'class' => 'CWebApplication',
	'config' => $config,
);
</pre>

You can use this module by setting params in your functional.suite.yml:
<pre>
class_name: TestGuy
modules:
    enabled: [Yii1, TestHelper]
    config:
        Yii1:
            appPath: '/path/to/index.php'
            url: 'http://localhost/path/to/index.php'
</pre>

You need to use CodeceptionHttpRequest from plugins directory (plugins\frameworks\yii\web), this component will be
imported when you include Yii1 module. There is also an alias "codeceptionsrc" available in Yii that points to the
codeception source directory, you can use it as always:
<pre>
Yii::getPathOfAlias('codeceptionsrc');
</pre>
This component extends yii CHttpRequest and handles headers() and cookie correctly. Also you can
modify it to be extended from your custom http-request component.

You can test this module by creating new empty Yii application and creating this scenario:
<pre>
$I = new TestGuy($scenario);
$I->wantTo('Test index page');
$I->amOnPage('/index.php');
$I->see('My Web Application','#header #logo');
$I->click('Login');
$I->see('Login','h1');
$I->see('Username');
$I->fillField('#LoginForm_username','demo');
$I->fillField('#LoginForm_password','demo');
$I->click('#login-form input[type="submit"]');
$I->seeLink('Logout (demo)');
$I->click('Logout (demo)');
$I->seeLink('Login');
</pre>
Then run codeception: php codecept.phar --steps run functional
You must see "OK" and that all steps are marked with asterisk (*).
Do not forget that after adding module in your functional.suite.yml you must run codeception "build" command.

 * property Codeception\Util\Connector\Yii1 $client

## Actions


### amOnPage


Opens the page.
Requires relative uri as parameter

Example:

``` php
<?php
// opens front page
$I->amOnPage('/');
// opens /register page
$I->amOnPage('/register');
?>
```

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


Perform a click on link or button.
Link or button are found by their names or CSS selector.
Submits a form if button is a submit type.

If link is an image it's found by alt attribute value of image.
If button is image button is found by it's value
If link or button can't be found by name they are searched by CSS selector.

Examples:

``` php
<?php
// simple link
$I->click('Logout');
// button of form
$I->click('Submit');
// CSS button
$I->click('#form input[type=submit]');
// XPath
$I->click('//form/*[@type=submit]')
?>
```
 * param $link


### createClient

__not documented__


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


### fillField


Fills a text field or textarea with value.

 * param $field
 * param $value


### formatResponse

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


Unticks a checkbox.

Example:

``` php
<?php
$I->uncheckOption('#notify');
?>
```

 * param $option
