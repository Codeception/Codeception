
## Codeception\Util\Locator


Set of useful functions for using CSS and XPath locators.
Please check them before writing complex functional or acceptance tests.


### Methods


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

 *  static
 *  param $selector1
 *  param $selector2
 *  throws \Exception
 *  return string

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Locator.php#L46)

Finds element by it's attribute(s)

 *  static

 *  param $element
 *  param $attributes

 *  return string

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Locator.php#L137)

Matches the *a* element with given URL

```php
<?php
use \Codeception\Util\Locator;

$I->see('Log In', Locator::href('/login.php'));
?>
```

 *  static
 *  param $url
 *  return string

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Locator.php#L73)

 *  param $selector
 *  return bool

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Locator.php#L154)

Checks that string and CSS selector for element by ID


[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Locator.php#L181)

Checks that locator is an XPath

 *  param $locator
 *  return bool

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Locator.php#L170)

Matches option by text

 *  param $value

 *  return string

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Locator.php#L109)

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

 *  static
 *  param $index
 *  return string

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Locator.php#L97)
