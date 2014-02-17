# Modules

Modules are high-level extensions that are used in tests. Modules are created for each test suites (according to suite configuration) and can be accessed directly from unit tests:

```php
<?php
$this->getModule('PhpBrowser')->client;
?>
```

or used inside scenario-driven tests, where `$I` acts as an wrapper to different modules

```php
<?php
$I->click(); // =>  PhpBrowser
$I->seeInDatabase(); // => Db
?>
```

Each module is extending `Codeception\Module` class and defined in `Codeception\Module` namespace. All Codeception modules are autoloaded by searching in this particular namespace: `PhpBrowser` => `Codeception\Module\PhpBrowser`. 

## What you should know before developing a module

The core principles:

1. Public methods of modules are actions of an actor inside a test. That's why they should be named in proper format:


```
doSomeStuff() => $I->doSomeStuff() => I do some stuff 
doSomeStuffWith($a, $b) => $I->doSomeStuffWith("vodka", "gin"); => I do some stuff with "vodka", "gin"
seeIsGreat() =>  $I->seeIsGreat() => I see is great
```

* Each method that define environment should start with `am` or `have` 
* Each assertion should start with `see` prefix
* Each method that returns values should start with `grab` (grabbers) or `have` (definitions)

Example:

```php
$I->amSeller();
$I->haveProducts(['vodka', 'gin']);
$I->haveDiscount('0.1');
$I->setPrice('gin', '10$');
$I->seePrice('gin', '9.9');
$price = $I->grabPriceFor('gin');
```

2. Configuration parameters are set in `.suite.yml` config and stored in `config` property array of a module. All default values can be set there as well. Required parameters should be set in `requiredFields` property. 

```php
<?php
protected $config = ['browser' => 'firefox'];
protected $requiredFields = ['url']; 
?>
```

You should not perform validation if `url` was set. Module would perform it for you, so you could access `$this->config['url']` inside a module.


3. If you use low-level clients in your module (PDO driver, framework client, selenium client) you should allow developers to access them. That's why you should define their instances as `public` properties of method.

Also you *may* provide a closure method to access low-leve API

```php
<?php
$I->executeInSelenium(function(\WebDriverClient $wb) {
    $wd->manage()->addCookie(['name' => 'verified']);
});
?>
```

4. Modules can be added to official repo, or published standalone. In any case module should be defined in `Codeception\Module` namespace. If you develop a module and you think it might be useful to others, please ask in Github Issues, maybe we would like to include it into the official repo.