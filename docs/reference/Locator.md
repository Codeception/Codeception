
## Codeception\Util\Locator



Set of useful functions for using CSS and XPath locators.
Please check them before writing complex functional or acceptance tests.



#### *public static* combine($selector1, $selector2) 

Applies OR operator to any number of CSS or XPath selectors.
You can mix up CSS and XPath selectors here.

```php
<?php
use \Codeception\Util\Locator;

$I->see('Title', Locator::combine('h1','h2','h3'));
?>
```

This will search for `Title` text in either `h1`, `h2`, or `h3` tag. You can also combine CSS selector with XPath locator:

```php
<?php
use \Codeception\Util\Locator;

$I->fillField(Locator::combine('form input[type=text]','//form/textarea[2]'), 'qwerty');
?>
```

As a result the Locator will produce a mixed XPath value that will be used in fillField action.

 * `static` 
 * `param` $selector1
 * `param` $selector2
 * `throws`  \Exception
 * `return`  string

[See source](https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Locator.php#L46)

#### *public static* find($element, array $attributes) 

Finds element by it's attribute(s)

 * `static` 

 * `param` $element
 * `param` $attributes

 * `return`  string

[See source](https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Locator.php#L137)

#### *public static* href($url) 

Matches the *a* element with given URL

```php
<?php
use \Codeception\Util\Locator;

$I->see('Log In', Locator::href('/login.php'));
?>
```

 * `static` 
 * `param` $url
 * `return`  string

[See source](https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Locator.php#L73)

#### *public static* isCSS($selector) 

 * `param` $selector
 * `return`  bool

[See source](https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Locator.php#L154)

#### *public static* isID($id) 

Checks that string and CSS selector for element by ID


[See source](https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Locator.php#L181)

#### *public static* isXPath($locator) 

Checks that locator is an XPath

 * `param` $locator
 * `return`  bool

[See source](https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Locator.php#L170)

#### *public static* option($value) 

Matches option by text

 * `param` $value

 * `return`  string

[See source](https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Locator.php#L109)

#### *public static* tabIndex($index) 

Matches the element with given tab index

Do you often use the `TAB` key to navigate through the web page? How do your site respond to this navigation?
You could try to match elements by their tab position using `tabIndex` method of `Locator` class.
```php
<?php
use \Codeception\Util\Locator;

$I->fillField(Locator::tabIndex(1), 'davert');
$I->fillField(Locator::tabIndex(2) , 'qwerty');
$I->click('Login');
?>
```

 * `static` 
 * `param` $index
 * `return`  string

[See source](https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Locator.php#L97)

<p>&nbsp;</p><div class="alert alert-warning">Reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Locator.php">Help us to improve documentation. Edit module reference</a></div>
