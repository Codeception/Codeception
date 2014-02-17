
## Codeception\Util\Maybe


Class to represent any type of content.
This class can act as an object, array, or string.
Method or property calls to this class won't cause any errors.

Maybe was used in Codeception 1.x to represent data on parsing step.
Not widely used in 2.0 anymore, but left for compatibility.

For instance, you may use `Codeception\Util\Maybe` as a test dummies.

```php
<?php
$user = new Maybe;
$user->posts->comments->count();
?>
```

### Methods


[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L86)

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L27)

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L57)

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L72)

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L41)

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L124)

(PHP 5 &gt;= 5.0.0)<br/>
Return the current element
 *  link http://php.net/manual/en/iterator.current.php
 *  return mixed Can return any type.

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L144)

(PHP 5 >= 5.4.0)
Serializes the object to a value that can be serialized natively by json_encode().
 *  link http://docs.php.net/manual/en/jsonserializable.jsonserialize.php
 *  return mixed Returns data which can be serialized by json_encode(), which is a value of any type other than a resource.

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L224)

(PHP 5 &gt;= 5.0.0)<br/>
Return the key of the current element
 *  link http://php.net/manual/en/iterator.key.php
 *  return mixed scalar on success, or null on failure.

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L174)

(PHP 5 &gt;= 5.0.0)<br/>
Move forward to next element
 *  link http://php.net/manual/en/iterator.next.php
 *  return void Any returned value is ignored.

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L163)

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L94)

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L102)

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L110)

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L117)

(PHP 5 &gt;= 5.0.0)<br/>
Rewind the Iterator to the first element
 *  link http://php.net/manual/en/iterator.rewind.php
 *  return void Any returned value is ignored.

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L210)

(PHP 5 &gt;= 5.0.0)<br/>
Checks if current position is valid
 *  link http://php.net/manual/en/iterator.valid.php
 *  return boolean The return value will be casted to boolean and then evaluated.
Returns true on success or false on failure.

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L191)
