


This module allows you to run tests inside Zend Framework.
It acts just like ControllerTestCase, but with usage of Codeception syntax.

It assumes, you have standard structure with __APPLICATION_PATH__ set to './application'
and LIBRARY_PATH set to './library'. If it's not then set the appropriate path in the Config.

[Tutorial](http://codeception.com/01-27-2012/bdd-with-zend-framework.html)

## Status

* Maintainer: **davert**
* Stability: **stable**
* Contact: codecept@davert.mail.ua

## Config

* env  - environment used for testing ('testing' by default).
* config - relative path to your application config ('application/configs/application.ini' by default).
* app_path - relative path to your application folder ('application' by default).
* lib_path - relative path to your library folder ('library' by default).

## API

* client - BrowserKit client
* db - current instance of Zend_Db_Adapter
* bootstrap - current bootstrap file.

## Cleaning up

For Doctrine1 and Doctrine2 all queries are put inside rollback transaction. If you are using one of this ORMs connect their modules to speed up testing.

Unfortunately Zend_Db doesn't support nested transactions, thus, for cleaning your database you should either use standard Db module or
[implement nested transactions yourself](http://blog.ekini.net/2010/03/05/zend-framework-how-to-use-nested-transactions-with-zend_db-and-mysql/).

If your database supports nested transactions (MySQL doesn't) or you implemented them you can put all your code inside a transaction.
Use a generated helper TestHelper. Use this code inside of it.

``` php
<?php
namespace Codeception\Module;
class TestHelper extends \Codeception\Module {
     function _before($test) {
         $this->getModule('ZF1')->db->beginTransaction();
     }

     function _after($test) {
         $this->getModule('ZF1')->db->rollback();
     }
}
?>
```

This will make your functional tests run super-fast.



### _findElements

*hidden API method, expected to be used from Helper classes*
 
Locates element using available Codeception locator types:

* XPath
* CSS
* Strict Locator

Use it in Helpers or GroupObject or Extension classes:

```php
<?php
$els = $this->getModule('ZF1')->_findElements('.items');
$els = $this->getModule('ZF1')->_findElements(['name' => 'username']);

$editLinks = $this->getModule('ZF1')->_findElements(['link' => 'Edit']);
// now you can iterate over $editLinks and check that all them have valid hrefs
```

WebDriver module returns `Facebook\WebDriver\Remote\RemoteWebElement` instances
PhpBrowser and Framework modules return `Symfony\Component\DomCrawler\Crawler` instances

 * `param` $locator
 * `return` array of interactive elements


### _savePageSource

*hidden API method, expected to be used from Helper classes*
 
Saves page source of to a file

```php
$this->getModule('ZF1')->_savePageSource(codecept_output_dir().'page.html');
```
 * `param` $filename


### amHttpAuthenticated
 
Authenticates user for HTTP_AUTH

 * `param` $username
 * `param` $password


### amOnPage
 
Opens the page for the given relative URI.

``` php
<?php
// opens front page
$I->amOnPage('/');
// opens /register page
$I->amOnPage('/register');
?>
```

 * `param` $page


### attachFile
 
Attaches a file relative to the Codeception data directory to the given file upload field.

``` php
<?php
// file is stored in 'tests/_data/prices.xls'
$I->attachFile('input[@type="file"]', 'prices.xls');
?>
```

 * `param` $field
 * `param` $filename


### checkOption
 
Ticks a checkbox. For radio buttons, use the `selectOption` method instead.

``` php
<?php
$I->checkOption('#agree');
?>
```

 * `param` $option


### click
 
Perform a click on a link or a button, given by a locator.
If a fuzzy locator is given, the page will be searched for a button, link, or image matching the locator string.
For buttons, the "value" attribute, "name" attribute, and inner text are searched.
For links, the link text is searched.
For images, the "alt" attribute and inner text of any parent links are searched.

The second parameter is a context (CSS or XPath locator) to narrow the search.

Note that if the locator matches a button of type `submit`, the form will be submitted.

``` php
<?php
// simple link
$I->click('Logout');
// button of form
$I->click('Submit');
// CSS button
$I->click('#form input[type=submit]');
// XPath
$I->click('//form/*[@type=submit]');
// link in context
$I->click('Logout', '#nav');
// using strict locator
$I->click(['link' => 'Login']);
?>
```

 * `param` $link
 * `param` $context


### dontSee
 
Checks that the current page doesn't contain the text specified.
Give a locator as the second parameter to match a specific region.

```php
<?php
$I->dontSee('Login'); // I can suppose user is already logged in
$I->dontSee('Sign Up','h1'); // I can suppose it's not a signup page
$I->dontSee('Sign Up','//body/h1'); // with XPath
?>
```

 * `param`      $text
 * `param null` $selector


### dontSeeCheckboxIsChecked
 
Check that the specified checkbox is unchecked.

``` php
<?php
$I->dontSeeCheckboxIsChecked('#agree'); // I suppose user didn't agree to terms
$I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user didn't check the first checkbox in form.
?>
```

 * `param` $checkbox


### dontSeeCookie
 
Checks that there isn't a cookie with the given name.
You can set additional cookie params like `domain`, `path` as array passed in last argument.

 * `param` $cookie

 * `param array` $params


### dontSeeCurrentUrlEquals
 
Checks that the current URL doesn't equal the given string.
Unlike `dontSeeInCurrentUrl`, this only matches the full URL.

``` php
<?php
// current url is not root
$I->dontSeeCurrentUrlEquals('/');
?>
```

 * `param` $uri


### dontSeeCurrentUrlMatches
 
Checks that current url doesn't match the given regular expression.

``` php
<?php
// to match root url
$I->dontSeeCurrentUrlMatches('~$/users/(\d+)~');
?>
```

 * `param` $uri


### dontSeeElement
 
Checks that the given element is invisible or not present on the page.
You can also specify expected attributes of this element.

``` php
<?php
$I->dontSeeElement('.error');
$I->dontSeeElement('//form/input[1]');
$I->dontSeeElement('input', ['name' => 'login']);
$I->dontSeeElement('input', ['value' => '123456']);
?>
```

 * `param` $selector
 * `param array` $attributes


### dontSeeInCurrentUrl
 
Checks that the current URI doesn't contain the given string.

``` php
<?php
$I->dontSeeInCurrentUrl('/users/');
?>
```

 * `param` $uri


### dontSeeInField
 
Checks that an input field or textarea doesn't contain the given value.
For fuzzy locators, the field is matched by label text, CSS and XPath.

``` php
<?php
$I->dontSeeInField('Body','Type your comment here');
$I->dontSeeInField('form textarea[name=body]','Type your comment here');
$I->dontSeeInField('form input[type=hidden]','hidden_value');
$I->dontSeeInField('#searchform input','Search');
$I->dontSeeInField('//form/*[@name=search]','Search');
$I->dontSeeInField(['name' => 'search'], 'Search');
?>
```

 * `param` $field
 * `param` $value


### dontSeeInFormFields
 
Checks if the array of form parameters (name => value) are not set on the form matched with
the passed selector.

``` php
<?php
$I->dontSeeInFormFields('form[name=myform]', [
     'input1' => 'non-existent value',
     'input2' => 'other non-existent value',
]);
?>
```

To check that an element hasn't been assigned any one of many values, an array can be passed
as the value:

``` php
<?php
$I->dontSeeInFormFields('.form-class', [
     'fieldName' => [
         'This value shouldn\'t be set',
         'And this value shouldn\'t be set',
     ],
]);
?>
```

Additionally, checkbox values can be checked with a boolean.

``` php
<?php
$I->dontSeeInFormFields('#form-id', [
     'checkbox1' => true,        // fails if checked
     'checkbox2' => false,       // fails if unchecked
]);
?>
```

 * `param` $formSelector
 * `param` $params


### dontSeeInTitle
 
Checks that the page title does not contain the given string.

 * `param` $title



### dontSeeLink
 
Checks that the page doesn't contain a link with the given string.
If the second parameter is given, only links with a matching "href" attribute will be checked.

``` php
<?php
$I->dontSeeLink('Logout'); // I suppose user is not logged in
$I->dontSeeLink('Checkout now', '/store/cart.php');
?>
```

 * `param` $text
 * `param null` $url


### dontSeeOptionIsSelected
 
Checks that the given option is not selected.

``` php
<?php
$I->dontSeeOptionIsSelected('#form input[name=payment]', 'Visa');
?>
```

 * `param` $selector
 * `param` $optionText



### fillField
 
Fills a text field or textarea with the given string.

``` php
<?php
$I->fillField("//input[@type='text']", "Hello World!");
$I->fillField(['name' => 'email'], 'jon@mail.com');
?>
```

 * `param` $field
 * `param` $value


### grabAttributeFrom
 
Grabs the value of the given attribute value from the given element.
Fails if element is not found.

``` php
<?php
$I->grabAttributeFrom('#tooltip', 'title');
?>
```


 * `param` $cssOrXpath
 * `param` $attribute
 * `internal param` $element


### grabCookie
 
Grabs a cookie value.
You can set additional cookie params like `domain`, `path` in array passed as last argument.

 * `param` $cookie

 * `param array` $params


### grabFromCurrentUrl
 
Executes the given regular expression against the current URI and returns the first match.
If no parameters are provided, the full URI is returned.

``` php
<?php
$user_id = $I->grabFromCurrentUrl('~$/user/(\d+)/~');
$uri = $I->grabFromCurrentUrl();
?>
```

 * `param null` $uri

 * `internal param` $url


### grabMultiple
__not documented__


### grabTextFrom
 
Finds and returns the text contents of the given element.
If a fuzzy locator is used, the element is found using CSS, XPath, and by matching the full page source by regular expression.

``` php
<?php
$heading = $I->grabTextFrom('h1');
$heading = $I->grabTextFrom('descendant-or-self::h1');
$value = $I->grabTextFrom('~<input value=(.*?)]~sgi'); // match with a regex
?>
```

 * `param` $cssOrXPathOrRegex



### grabValueFrom
 
 * `param` $field

 * `return` array|mixed|null|string


### resetCookie
 
Unsets cookie with the given name.
You can set additional cookie params like `domain`, `path` in array passed as last argument.

 * `param` $cookie

 * `param array` $params


### see
 
Checks that the current page contains the given string.
Specify a locator as the second parameter to match a specific region.

``` php
<?php
$I->see('Logout'); // I can suppose user is logged in
$I->see('Sign Up','h1'); // I can suppose it's a signup page
$I->see('Sign Up','//body/h1'); // with XPath
?>
```

 * `param`      $text
 * `param null` $selector


### seeCheckboxIsChecked
 
Checks that the specified checkbox is checked.

``` php
<?php
$I->seeCheckboxIsChecked('#agree'); // I suppose user agreed to terms
$I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user agreed to terms, If there is only one checkbox in form.
$I->seeCheckboxIsChecked('//form/input[@type=checkbox and @name=agree]');
?>
```

 * `param` $checkbox


### seeCookie
 
Checks that a cookie with the given name is set.
You can set additional cookie params like `domain`, `path` as array passed in last argument.

``` php
<?php
$I->seeCookie('PHPSESSID');
?>
```

 * `param` $cookie
 * `param array` $params


### seeCurrentUrlEquals
 
Checks that the current URL is equal to the given string.
Unlike `seeInCurrentUrl`, this only matches the full URL.

``` php
<?php
// to match root url
$I->seeCurrentUrlEquals('/');
?>
```

 * `param` $uri


### seeCurrentUrlMatches
 
Checks that the current URL matches the given regular expression.

``` php
<?php
// to match root url
$I->seeCurrentUrlMatches('~$/users/(\d+)~');
?>
```

 * `param` $uri


### seeElement
 
Checks that the given element exists on the page and is visible.
You can also specify expected attributes of this element.

``` php
<?php
$I->seeElement('.error');
$I->seeElement('//form/input[1]');
$I->seeElement('input', ['name' => 'login']);
$I->seeElement('input', ['value' => '123456']);

// strict locator in first arg, attributes in second
$I->seeElement(['css' => 'form input'], ['name' => 'login']);
?>
```

 * `param` $selector
 * `param array` $attributes
@return


### seeInCurrentUrl
 
Checks that current URI contains the given string.

``` php
<?php
// to match: /home/dashboard
$I->seeInCurrentUrl('home');
// to match: /users/1
$I->seeInCurrentUrl('/users/');
?>
```

 * `param` $uri


### seeInField
 
Checks that the given input field or textarea contains the given value.
For fuzzy locators, fields are matched by label text, the "name" attribute, CSS, and XPath.

``` php
<?php
$I->seeInField('Body','Type your comment here');
$I->seeInField('form textarea[name=body]','Type your comment here');
$I->seeInField('form input[type=hidden]','hidden_value');
$I->seeInField('#searchform input','Search');
$I->seeInField('//form/*[@name=search]','Search');
$I->seeInField(['name' => 'search'], 'Search');
?>
```

 * `param` $field
 * `param` $value


### seeInFormFields
 
Checks if the array of form parameters (name => value) are set on the form matched with the
passed selector.

``` php
<?php
$I->seeInFormFields('form[name=myform]', [
     'input1' => 'value',
     'input2' => 'other value',
]);
?>
```

For multi-select elements, or to check values of multiple elements with the same name, an
array may be passed:

``` php
<?php
$I->seeInFormFields('.form-class', [
     'multiselect' => [
         'value1',
         'value2',
     ],
     'checkbox[]' => [
         'a checked value',
         'another checked value',
     ],
]);
?>
```

Additionally, checkbox values can be checked with a boolean.

``` php
<?php
$I->seeInFormFields('#form-id', [
     'checkbox1' => true,        // passes if checked
     'checkbox2' => false,       // passes if unchecked
]);
?>
```

Pair this with submitForm for quick testing magic.

``` php
<?php
$form = [
     'field1' => 'value',
     'field2' => 'another value',
     'checkbox1' => true,
     // ...
];
$I->submitForm('//form[@id=my-form]', $form, 'submitButton');
// $I->amOnPage('/path/to/form-page') may be needed
$I->seeInFormFields('//form[@id=my-form]', $form);
?>
```

 * `param` $formSelector
 * `param` $params


### seeInTitle
 
Checks that the page title contains the given string.

``` php
<?php
$I->seeInTitle('Blog - Post #1');
?>
```

 * `param` $title



### seeLink
 
Checks that there's a link with the specified text.
Give a full URL as the second parameter to match links with that exact URL.

``` php
<?php
$I->seeLink('Logout'); // matches <a href="#">Logout</a>
$I->seeLink('Logout','/logout'); // matches <a href="/logout">Logout</a>
?>
```

 * `param`      $text
 * `param null` $url


### seeNumberOfElements
 
Checks that there are a certain number of elements matched by the given locator on the page.

``` php
<?php
$I->seeNumberOfElements('tr', 10);
$I->seeNumberOfElements('tr', [0,10]); //between 0 and 10 elements
?>
```
 * `param` $selector
 * `param mixed` $expected :
- string: strict number
- array: range of numbers [0,10]


### seeOptionIsSelected
 
Checks that the given option is selected.

``` php
<?php
$I->seeOptionIsSelected('#form input[name=payment]', 'Visa');
?>
```

 * `param` $selector
 * `param` $optionText



### seePageNotFound
 
Asserts that current page has 404 response status code.


### seeResponseCodeIs
 
Checks that response code is equal to value provided.

 * `param` $code



### selectOption
 
Selects an option in a select tag or in radio button group.

``` php
<?php
$I->selectOption('form select[name=account]', 'Premium');
$I->selectOption('form input[name=payment]', 'Monthly');
$I->selectOption('//form/select[@name=account]', 'Monthly');
?>
```

Provide an array for the second argument to select multiple options:

``` php
<?php
$I->selectOption('Which OS do you use?', array('Windows','Linux'));
?>
```

 * `param` $select
 * `param` $option


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
$I->sendAjaxPostRequest('/updateSettings', array('notifications' => true)); // POST
$I->sendAjaxGetRequest('/updateSettings', array('notifications' => true)); // GET

```

 * `param` $uri
 * `param` $params


### sendAjaxRequest
 
If your page triggers an ajax request, you can perform it manually.
This action sends an ajax request with specified method and params.

Example:

You need to perform an ajax request specifying the HTTP method.

``` php
<?php
$I->sendAjaxRequest('PUT', '/posts/7', array('title' => 'new title'));

```

 * `param` $method
 * `param` $uri
 * `param` $params


### setCookie
 
Sets a cookie with the given name and value.
You can set additional cookie params like `domain`, `path`, `expire`, `secure` in array passed as last argument.

``` php
<?php
$I->setCookie('PHPSESSID', 'el4ukv0kqbvoirg7nkp4dncpk3');
?>
```

 * `param` $name
 * `param` $val
 * `param array` $params



### submitForm
 
Submits the given form on the page, optionally with the given form
values.  Give the form fields values as an array.

Skipped fields will be filled by their values from the page.
You don't need to click the 'Submit' button afterwards.
This command itself triggers the request to form's action.

You can optionally specify what button's value to include
in the request with the last parameter as an alternative to
explicitly setting its value in the second parameter, as
button values are not otherwise included in the request.

Examples:

``` php
<?php
$I->submitForm('#login', [
    'login' => 'davert',
    'password' => '123456'
]);
// or
$I->submitForm('#login', [
    'login' => 'davert',
    'password' => '123456'
], 'submitButtonName');

```

For example, given this sample "Sign Up" form:

``` html
<form action="/sign_up">
    Login:
    <input type="text" name="user[login]" /><br/>
    Password:
    <input type="password" name="user[password]" /><br/>
    Do you agree to our terms?
    <input type="checkbox" name="user[agree]" /><br/>
    Select pricing plan:
    <select name="plan">
        <option value="1">Free</option>
        <option value="2" selected="selected">Paid</option>
    </select>
    <input type="submit" name="submitButton" value="Submit" />
</form>
```

You could write the following to submit it:

``` php
<?php
$I->submitForm(
    '#userForm',
    [
        'user' => [
            'login' => 'Davert',
            'password' => '123456',
            'agree' => true
        ]
    ],
    'submitButton'
);
```
Note that "2" will be the submitted value for the "plan" field, as it is
the selected option.

You can also emulate a JavaScript submission by not specifying any
buttons in the third parameter to submitForm.

```php
<?php
$I->submitForm(
    '#userForm',
    [
        'user' => [
            'login' => 'Davert',
            'password' => '123456',
            'agree' => true
        ]
    ]
);
```

Pair this with seeInFormFields for quick testing magic.

``` php
<?php
$form = [
     'field1' => 'value',
     'field2' => 'another value',
     'checkbox1' => true,
     // ...
];
$I->submitForm('//form[@id=my-form]', $form, 'submitButton');
// $I->amOnPage('/path/to/form-page') may be needed
$I->seeInFormFields('//form[@id=my-form]', $form);
?>
```

Parameter values can be set to arrays for multiple input fields
of the same name, or multi-select combo boxes.  For checkboxes,
either the string value can be used, or boolean values which will
be replaced by the checkbox's value in the DOM.

``` php
<?php
$I->submitForm('#my-form', [
     'field1' => 'value',
     'checkbox' => [
         'value of first checkbox',
         'value of second checkbox,
     ],
     'otherCheckboxes' => [
         true,
         false,
         false
     ],
     'multiselect' => [
         'first option value',
         'second option value'
     ]
]);
?>
```

Mixing string and boolean values for a checkbox's value is not supported
and may produce unexpected results.

Field names ending in "[]" must be passed without the trailing square 
bracket characters, and must contain an array for its value.  This allows
submitting multiple values with the same name, consider:

```php
$I->submitForm('#my-form', [
    'field[]' => 'value',
    'field[]' => 'another value', // 'field[]' is already a defined key
]);
```

The solution is to pass an array value:

```php
// this way both values are submitted
$I->submitForm('#my-form', [
    'field' => [
        'value',
        'another value',
    ]
]);
```

 * `param` $selector
 * `param` $params
 * `param` $button


### uncheckOption
 
Unticks a checkbox.

``` php
<?php
$I->uncheckOption('#notify');
?>
```

 * `param` $option

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.1/src/Codeception/Module/ZF1.php">Help us to improve documentation. Edit module reference</a></div>
