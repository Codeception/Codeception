# AngularJS


Module for AngularJS testing, based on [WebDriver module](http://codeception.com/docs/modules/WebDriver) and [Protractor](http://angular.github.io/protractor/).

Performs **synchronization to ensure that page content is fully rendered**.
Uses Angular's and Protractor internals methods to synchronize with the page.

## Configurarion

The same as for [WebDriver](http://codeception.com/docs/modules/WebDriver#Configuration), but few new options added:

* `el` - element where Angular application is defined (default: `body`)
* `script_timeout` - for how long in seconds to wait for angular operations to finish (default: 5)

### Example (`acceptance.suite.yml`)

    modules:
       enabled:
          - AngularJS:
             url: 'http://localhost/'
             browser: firefox
             script_timeout: 10


### Additional Features

Can perform matching elements by model. In this case you should provide a strict locator with `model` set.

Example:

```php
$I->selectOption(['model' => 'customerId'], '3');
```


## Actions

### _findElements

*hidden API method, expected to be used from Helper classes*
 
Locates element using available Codeception locator types:

* XPath
* CSS
* Strict Locator

Use it in Helpers or GroupObject or Extension classes:

```php
<?php
$els = $this->getModule('AngularJS')->_findElements('.items');
$els = $this->getModule('AngularJS')->_findElements(['name' => 'username']);

$editLinks = $this->getModule('AngularJS')->_findElements(['link' => 'Edit']);
// now you can iterate over $editLinks and check that all them have valid hrefs
```

WebDriver module returns `Facebook\WebDriver\Remote\RemoteWebElement` instances
PhpBrowser and Framework modules return `Symfony\Component\DomCrawler\Crawler` instances

 * `param` $locator
 * `return` array of interactive elements


### _getCurrentUri

*hidden API method, expected to be used from Helper classes*
 
Uri of currently opened page.
 * `return` string
 * `throws`  ModuleException


### _getUrl

*hidden API method, expected to be used from Helper classes*
 
Returns URL of a host.
 * `throws`  ModuleConfigException


### _savePageSource

*hidden API method, expected to be used from Helper classes*
 
Saves HTML source of a page to a file
 * `param` $filename


### _saveScreenshot

*hidden API method, expected to be used from Helper classes*
 
Saves screenshot of current page to a file

```php
$this->getModule('AngularJS')->_saveScreenshot(codecept_output_dir().'screenshot_1.png');
```
 * `param` $filename


### acceptPopup
 
Accepts the active JavaScript native popup window, as created by `window.alert`|`window.confirm`|`window.prompt`.
Don't confuse popups with modal windows,
as created by [various libraries](http://jster.net/category/windows-modals-popups).


### amInsideAngularApp
 
Enables Angular mode (enabled by default).
Waits for Angular to finish rendering after each action.


### amOnPage
 
Opens the page for the given relative URI.

``` php
<?php
// opens front page
$I->amOnPage('/');
// opens /register page
$I->amOnPage('/register');
```

 * `param` $page


### amOnSubdomain
 
Changes the subdomain for the 'url' configuration parameter.
Does not open a page; use `amOnPage` for that.

``` php
<?php
// If config is: 'http://mysite.com'
// or config is: 'http://www.mysite.com'
// or config is: 'http://company.mysite.com'

$I->amOnSubdomain('user');
$I->amOnPage('/');
// moves to http://user.mysite.com/
?>
```

 * `param` $subdomain



### amOnUrl
 
Open web page at the given absolute URL and sets its hostname as the base host.

``` php
<?php
$I->amOnUrl('http://codeception.com');
$I->amOnPage('/quickstart'); // moves to http://codeception.com/quickstart
?>
```


### amOutsideAngularApp
 
Disabled Angular mode.

Falls back to original WebDriver, in case web page does not contain Angular app.


### appendField
 
Append the given text to the given element.
Can also add a selection to a select box.

``` php
<?php
$I->appendField('#mySelectbox', 'SelectValue');
$I->appendField('#myTextField', 'appended');
?>
```

 * `param string` $field
 * `param string` $value
 * `throws`  \Codeception\Exception\ElementNotFound


### attachFile
 
Attaches a file relative to the Codeception data directory to the given file upload field.

``` php
<?php
// file is stored in 'tests/_data/prices.xls'
$I->attachFile('input[ * `type="file"]',`  'prices.xls');
?>
```

 * `param` $field
 * `param` $filename


### cancelPopup
 
Dismisses the active JavaScript popup, as created by `window.alert`|`window.confirm`|`window.prompt`.


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
$I->click('//form/*[ * `type=submit]');` 
// link in context
$I->click('Logout', '#nav');
// using strict locator
$I->click(['link' => 'Login']);
?>
```

 * `param` $link
 * `param` $context


### clickWithRightButton
 
Performs contextual click with the right mouse button on an element.

 * `param` $cssOrXPath
 * `throws`  \Codeception\Exception\ElementNotFound


### debugWebDriverLogs
 
Print out latest Selenium Logs in debug mode


### dontSee
 
Checks that the current page doesn't contain the text specified (case insensitive).
Give a locator as the second parameter to match a specific region.

```php
<?php
$I->dontSee('Login');                    // I can suppose user is already logged in
$I->dontSee('Sign Up','h1');             // I can suppose it's not a signup page
$I->dontSee('Sign Up','//body/h1');      // with XPath
```

Note that the search is done after stripping all HTML tags from the body,
so `$I->dontSee('strong')` will fail on strings like:

  - `<p>I am Stronger than thou</p>`
  - `<script>document.createElement('strong');</script>`

But will ignore strings like:

  - `<strong>Home</strong>`
  - `<div class="strong">Home</strong>`
  - `<!-- strong -->`

For checking the raw source code, use `seeInSource()`.

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


### dontSeeElementInDOM
 
Opposite of `seeElementInDOM`.

 * `param` $selector


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
$I->dontSeeInField('//form/*[ * `name=search]','Search');` 
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


### dontSeeInPageSource
 
Checks that the page source doesn't contain the given string.

 * `param` $text


### dontSeeInSource
 
Checks that the current page contains the given string in its
raw source code.

```php
<?php
$I->dontSeeInSource('<h1>Green eggs &amp; ham</h1>');
```

 * `param`      $raw


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



### doubleClick
 
Performs a double-click on an element matched by CSS or XPath.

 * `param` $cssOrXPath
 * `throws`  \Codeception\Exception\ElementNotFound


### dragAndDrop
 
Performs a simple mouse drag-and-drop operation.

``` php
<?php
$I->dragAndDrop('#drag', '#drop');
?>
```

 * `param string` $source (CSS ID or XPath)
 * `param string` $target (CSS ID or XPath)


### executeInSelenium
 
Low-level API method.
If Codeception commands are not enough, this allows you to use Selenium WebDriver methods directly:

``` php
$I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
  $webdriver->get('http://google.com');
});
```

This runs in the context of the
[RemoteWebDriver class](https://github.com/facebook/php-webdriver/blob/master/lib/remote/RemoteWebDriver.php).
Try not to use this command on a regular basis.
If Codeception lacks a feature you need, please implement it and submit a patch.

 * `param callable` $function


### executeJS
 
Executes custom JavaScript.

This example uses jQuery to get a value and assigns that value to a PHP variable:

```php
<?php
$myVar = $I->executeJS('return $("#myField").val()');
?>
```

 * `param` $script


### fillField
 
Fills a text field or textarea with the given string.

``` php
<?php
$I->fillField("//input[ * `type='text']",`  "Hello World!");
$I->fillField(['name' => 'email'], 'jon * `mail.com');` 
?>
```

 * `param` $field
 * `param` $value


### getVisibleText
 
Grabs all visible text from the current page.

 * `return` string


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



### grabMultiple
 
Grabs either the text content, or attribute values, of nodes
matched by $cssOrXpath and returns them as an array.

```html
<a href="#first">First</a>
<a href="#second">Second</a>
<a href="#third">Third</a>
```

```php
<?php
// would return ['First', 'Second', 'Third']
$aLinkText = $I->grabMultiple('a');

// would return ['#first', '#second', '#third']
$aLinks = $I->grabMultiple('a', 'href');
?>
```

 * `param` $cssOrXpath
 * `param` $attribute
 * `return` string[]


### grabTextFrom
 
Finds and returns the text contents of the given element.
If a fuzzy locator is used, the element is found using CSS, XPath,
and by matching the full page source by regular expression.

``` php
<?php
$heading = $I->grabTextFrom('h1');
$heading = $I->grabTextFrom('descendant-or-self::h1');
$value = $I->grabTextFrom('~<input value=(.*?)]~sgi'); // match with a regex
?>
```

 * `param` $cssOrXPathOrRegex



### grabValueFrom
 
Finds the value for the given form field.
If a fuzzy locator is used, the field is found by field name, CSS, and XPath.

``` php
<?php
$name = $I->grabValueFrom('Name');
$name = $I->grabValueFrom('input[name=username]');
$name = $I->grabValueFrom('descendant-or-self::form/descendant::input[ * `name`  = 'username']');
$name = $I->grabValueFrom(['name' => 'username']);
?>
```

 * `param` $field



### loadSessionSnapshot
 
 * `param string` $name
 * `return` bool


### makeScreenshot
 
Takes a screenshot of the current window and saves it to `tests/_output/debug`.

``` php
<?php
$I->amOnPage('/user/edit');
$I->makeScreenshot('edit_page');
// saved to: tests/_output/debug/edit_page.png
?>
```

 * `param` $name


### maximizeWindow
 
Maximizes the current window.


### moveBack
 
Moves back in history.


### moveForward
 
Moves forward in history.


### moveMouseOver
 
Move mouse over the first element matched by the given locator.
If the second and third parameters are given,
then the mouse is moved to an offset of the element's top-left corner.
Otherwise, the mouse is moved to the center of the element.

``` php
<?php
$I->moveMouseOver(['css' => '.checkout'], 20, 50);
?>
```

 * `param string` $cssOrXPath css or xpath of the web element
 * `param int` $offsetX
 * `param int` $offsetY

 * `throws`  \Codeception\Exception\ElementNotFound


### pauseExecution
 
Pauses test execution in debug mode.
To proceed test press "ENTER" in console.

This method is useful while writing tests,
since it allows you to inspect the current page in the middle of a test case.


### pressKey
 
Presses the given key on the given element.
To specify a character and modifier (e.g. ctrl, alt, shift, meta), pass an array for $char with
the modifier as the first element and the character as the second.
For special keys use key constants from WebDriverKeys class.

``` php
<?php
// <input id="page" value="old" />
$I->pressKey('#page','a'); // => olda
$I->pressKey('#page',array('ctrl','a'),'new'); //=> new
$I->pressKey('#page',array('shift','111'),'1','x'); //=> old!!!1x
$I->pressKey('descendant-or-self::*[ * `id='page']','u');`  //=> oldu
$I->pressKey('#name', array('ctrl', 'a'), \Facebook\WebDriver\WebDriverKeys::DELETE); //=>''
?>
```

 * `param` $element
 * `param` $char string|array Can be char or array with modifier. You can provide several chars.
 * `throws`  \Codeception\Exception\ElementNotFound


### reloadPage
 
Reloads the current page.


### resetCookie
 
Unsets cookie with the given name.
You can set additional cookie params like `domain`, `path` in array passed as last argument.

 * `param` $cookie

 * `param array` $params


### resizeWindow
 
Resize the current window.

``` php
<?php
$I->resizeWindow(800, 600);

```

 * `param int` $width
 * `param int` $height


### saveSessionSnapshot
 
 * `param string` $name


### scrollTo
 
Move to the middle of the given element matched by the given locator.
Extra shift, calculated from the top-left corner of the element,
can be set by passing $offsetX and $offsetY parameters.

``` php
<?php
$I->scrollTo(['css' => '.checkout'], 20, 50);
?>
```

 * `param` $selector
 * `param int` $offsetX
 * `param int` $offsetY


### see
 
Checks that the current page contains the given string (case insensitive).

You can specify a specific HTML element (via CSS or XPath) as the second
parameter to only search within that element.

``` php
<?php
$I->see('Logout');                 // I can suppose user is logged in
$I->see('Sign Up', 'h1');          // I can suppose it's a signup page
$I->see('Sign Up', '//body/h1');   // with XPath
```

Note that the search is done after stripping all HTML tags from the body,
so `$I->see('strong')` will return true for strings like:

  - `<p>I am Stronger than thou</p>`
  - `<script>document.createElement('strong');</script>`

But will *not* be true for strings like:

  - `<strong>Home</strong>`
  - `<div class="strong">Home</strong>`
  - `<!-- strong -->`

For checking the raw source code, use `seeInSource()`.

 * `param`      $text
 * `param null` $selector


### seeCheckboxIsChecked
 
Checks that the specified checkbox is checked.

``` php
<?php
$I->seeCheckboxIsChecked('#agree'); // I suppose user agreed to terms
$I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user agreed to terms, If there is only one checkbox in form.
$I->seeCheckboxIsChecked('//form/input[ * `type=checkbox`  and  * `name=agree]');` 
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
 * `return` 


### seeElementInDOM
 
Checks that the given element exists on the page, even it is invisible.

``` php
<?php
$I->seeElementInDOM('//form/input[type=hidden]');
?>
```

 * `param` $selector


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
$I->seeInField('//form/*[ * `name=search]','Search');` 
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
$I->submitForm('//form[ * `id=my-form]',`  $form, 'submitButton');
// $I->amOnPage('/path/to/form-page') may be needed
$I->seeInFormFields('//form[ * `id=my-form]',`  $form);
?>
```

 * `param` $formSelector
 * `param` $params


### seeInPageSource
 
Checks that the page source contains the given string.

```php
<?php
$I->seeInPageSource('<link rel="apple-touch-icon"');
```

 * `param` $text


### seeInPopup
 
Checks that the active JavaScript popup,
as created by `window.alert`|`window.confirm`|`window.prompt`, contains the given string.

 * `param` $text


### seeInSource
 
Checks that the current page contains the given string in its
raw source code.

``` php
<?php
$I->seeInSource('<h1>Green eggs &amp; ham</h1>');
```

 * `param`      $raw


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


### seeNumberOfElementsInDOM
__not documented__


### seeOptionIsSelected
 
Checks that the given option is selected.

``` php
<?php
$I->seeOptionIsSelected('#form input[name=payment]', 'Visa');
?>
```

 * `param` $selector
 * `param` $optionText



### selectOption
 
Selects an option in a select tag or in radio button group.

``` php
<?php
$I->selectOption('form select[name=account]', 'Premium');
$I->selectOption('form input[name=payment]', 'Monthly');
$I->selectOption('//form/select[ * `name=account]',`  'Monthly');
?>
```

Provide an array for the second argument to select multiple options:

``` php
<?php
$I->selectOption('Which OS do you use?', array('Windows','Linux'));
?>
```

Or provide an associative array for the second argument to specifically define which selection method should be used:

``` php
<?php
$I->selectOption('Which OS do you use?', array('text' => 'Windows')); // Only search by text 'Windows'
$I->selectOption('Which OS do you use?', array('value' => 'windows')); // Only search by value 'windows'
?>
```

 * `param` $select
 * `param` $option


### setCookie
 
Sets a cookie with the given name and value.
You can set additional cookie params like `domain`, `path`, `expires`, `secure` in array passed as last argument.

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
values.  Give the form fields values as an array. Note that hidden fields
can't be accessed.

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
        'user[login]' => 'Davert',
        'user[password]' => '123456',
        'user[agree]' => true
    ],
    'submitButton'
);
```
Note that "2" will be the submitted value for the "plan" field, as it is
the selected option.

Also note that this differs from PhpBrowser, in that
```'user' => [ 'login' => 'Davert' ]``` is not supported at the moment.
Named array keys *must* be included in the name as above.

Pair this with seeInFormFields for quick testing magic.

``` php
<?php
$form = [
     'field1' => 'value',
     'field2' => 'another value',
     'checkbox1' => true,
     // ...
];
$I->submitForm('//form[ * `id=my-form]',`  $form, 'submitButton');
// $I->amOnPage('/path/to/form-page') may be needed
$I->seeInFormFields('//form[ * `id=my-form]',`  $form);
?>
```

Parameter values must be set to arrays for multiple input fields
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

The `$button` parameter can be either a string, an array or an instance
of Facebook\WebDriver\WebDriverBy. When it is a string, the
button will be found by its "name" attribute. If $button is an
array then it will be treated as a strict selector and a WebDriverBy
will be used verbatim.

For example, given the following HTML:

``` html
<input type="submit" name="submitButton" value="Submit" />
```

`$button` could be any one of the following:
  - 'submitButton'
  - ['name' => 'submitButton']
  - WebDriverBy::name('submitButton')

 * `param` $selector
 * `param` $params
 * `param` $button


### switchToIFrame
 
Switch to another frame on the page.

Example:
``` html
<iframe name="another_frame" src="http://example.com">

```

``` php
<?php
# switch to iframe
$I->switchToIFrame("another_frame");
# switch to parent page
$I->switchToIFrame();

```

 * `param string|null` $name


### switchToWindow
 
Switch to another window identified by name.

The window can only be identified by name. If the $name parameter is blank, the parent window will be used.

Example:
``` html
<input type="button" value="Open window" onclick="window.open('http://example.com', 'another_window')">
```

``` php
<?php
$I->click("Open window");
# switch to another window
$I->switchToWindow("another_window");
# switch to parent window
$I->switchToWindow();
?>
```

If the window has no name, the only way to access it is via the `executeInSelenium()` method, like so:

``` php
<?php
$I->executeInSelenium(function (\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
     $handles=$webdriver->getWindowHandles();
     $last_window = end($handles);
     $webdriver->switchTo()->window($last_window);
});
?>
```

 * `param string|null` $name


### typeInPopup
 
Enters text into a native JavaScript prompt popup, as created by `window.prompt`.

 * `param` $keys


### uncheckOption
 
Unticks a checkbox.

``` php
<?php
$I->uncheckOption('#notify');
?>
```

 * `param` $option


### unselectOption
__not documented__


### wait
 
Wait for $timeout seconds.

 * `param int` $timeout secs
 * `throws`  \Codeception\Exception\TestRuntimeException


### waitForElement
 
Waits up to $timeout seconds for an element to appear on the page.
If the element doesn't appear, a timeout exception is thrown.

``` php
<?php
$I->waitForElement('#agree_button', 30); // secs
$I->click('#agree_button');
?>
```

 * `param` $element
 * `param int` $timeout seconds
 * `throws`  \Exception


### waitForElementChange
 
Waits up to $timeout seconds for the given element to change.
Element "change" is determined by a callback function which is called repeatedly
until the return value evaluates to true.

``` php
<?php
use \Facebook\WebDriver\WebDriverElement
$I->waitForElementChange('#menu', function(WebDriverElement $el) {
    return $el->isDisplayed();
}, 100);
?>
```

 * `param` $element
 * `param \Closure` $callback
 * `param int` $timeout seconds
 * `throws`  \Codeception\Exception\ElementNotFound


### waitForElementNotVisible
 
Waits up to $timeout seconds for the given element to become invisible.
If element stays visible, a timeout exception is thrown.

``` php
<?php
$I->waitForElementNotVisible('#agree_button', 30); // secs
?>
```

 * `param` $element
 * `param int` $timeout seconds
 * `throws`  \Exception


### waitForElementVisible
 
Waits up to $timeout seconds for the given element to be visible on the page.
If element doesn't appear, a timeout exception is thrown.

``` php
<?php
$I->waitForElementVisible('#agree_button', 30); // secs
$I->click('#agree_button');
?>
```

 * `param` $element
 * `param int` $timeout seconds
 * `throws`  \Exception


### waitForJS
 
Executes JavaScript and waits up to $timeout seconds for it to return true.

In this example we will wait up to 60 seconds for all jQuery AJAX requests to finish.

``` php
<?php
$I->waitForJS("return $.active == 0;", 60);
?>
```

 * `param string` $script
 * `param int` $timeout seconds


### waitForText
 
Waits up to $timeout seconds for the given string to appear on the page.
Can also be passed a selector to search in.
If the given text doesn't appear, a timeout exception is thrown.

``` php
<?php
$I->waitForText('foo', 30); // secs
$I->waitForText('foo', 30, '.title'); // secs
?>
```

 * `param string` $text
 * `param int` $timeout seconds
 * `param null` $selector
 * `throws`  \Exception

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.2/src/Codeception/Module/AngularJS.php">Help us to improve documentation. Edit module reference</a></div>
