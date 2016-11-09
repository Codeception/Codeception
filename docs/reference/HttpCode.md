
## Codeception\Util\HttpCode



Class containing constants of HTTP Status Codes
and method to print HTTP code with its description.

Usage:

```php
<?php
use \Codeception\Util\HttpCode;

// using REST, PhpBrowser, or any Framework module
$I->seeResponseCodeIs(HttpCode::OK);
$I->dontSeeResponseCodeIs(HttpCode::NOT_FOUND);
```




#### getDescription()

 *public static* getDescription($code) 

Returns string with HTTP code and its description

```php
<?php
HttpCode::getDescription(200); // '200 (OK)'
HttpCode::getDescription(401); // '401 (Unauthorized)'
```

 * `param` $code
 * `return` mixed

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/HttpCode.php#L155)

<p>&nbsp;</p><div class="alert alert-warning">Reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/blob/2.2/src//Codeception/Util/HttpCode.php">Help us to improve documentation. Edit module reference</a></div>
