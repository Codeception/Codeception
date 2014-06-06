
## Codeception\Util\Fixtures



Really basic class to store data in global array and use it in Cests/Tests.

```php
<?php
Fixtures::add('user1', ['name' => 'davert']);
Fixtures::get('user1');

?>
```


#### *public static* add($name, $data) 

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Fixtures.php#L21)

#### *public static* cleanup() 

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Fixtures.php#L35)

#### *public static* get($name) 

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Fixtures.php#L26)

