
## Codeception\Util\Fixtures


Really basic class to store data in global array and use it in Cests/Tests.

```php
<?php
Fixtures::add('user1', ['name' => 'davert']);
Fixtures::get('user1');

?>
```


### Methods


#### *public static* add
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Fixtures.php#L21)

#### *public static* cleanup
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Fixtures.php#L35)

#### *public static* get
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Fixtures.php#L26)
