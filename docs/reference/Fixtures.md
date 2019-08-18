
## Codeception\Util\Fixtures



Really basic class to store data in global array and use it in Cests/Tests.

```php
<?php
Fixtures::add('user1', ['name' => 'davert']);
Fixtures::get('user1');
Fixtures::exists('user1');

?>
```



#### add()

 *public static* add($name, $data) 

[See source](https://github.com/Codeception/Codeception/blob/3.0/src/Codeception/Util/Fixtures.php#L21)

#### cleanup()

 *public static* cleanup() 

[See source](https://github.com/Codeception/Codeception/blob/3.0/src/Codeception/Util/Fixtures.php#L35)

#### exists()

 *public static* exists($name) 

[See source](https://github.com/Codeception/Codeception/blob/3.0/src/Codeception/Util/Fixtures.php#L40)

#### get()

 *public static* get($name) 

[See source](https://github.com/Codeception/Codeception/blob/3.0/src/Codeception/Util/Fixtures.php#L26)

<p>&nbsp;</p><div class="alert alert-warning">Reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/blob/3.0/src//Codeception/Util/Fixtures.php">Help us to improve documentation. Edit module reference</a></div>
