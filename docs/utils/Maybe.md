
## Codeception\Util\Maybe


* *Implements* `ArrayAccess`, `Iterator`, `Traversable`, `JsonSerializable`

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



#### *public* __construct#### *public* __construct($val = null)
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L27)


#### *public* __toString#### *public* __toString()
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L41)

#### *public* __get#### *public* __get($key)
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L57)

#### *public* __set#### *public* __set($key, $val)
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L72)

#### *public* __call#### *public* __call($method, $args)
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L86)

#### *public* __clone#### *public* __clone()
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L94)

#### *public* __unset#### *public* __unset($key)
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L101)

#### *public* offsetExists#### *public* offsetExists($offset)
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L111)

#### *public* offsetGet#### *public* offsetGet($offset)
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L119)

#### *public* offsetSet#### *public* offsetSet($offset, $value)
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L127)

#### *public* offsetUnset#### *public* offsetUnset($offset)
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L134)

#### *public* __value#### *public* __value()
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L141)

#### *public* current#### *public* current()(PHP 5 &gt;= 5.0.0)<br/>
Return the current element
 * `link`  http://php.net/manual/en/iterator.current.php
 * `return`  mixed Can return any type.
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L161)

#### *public* next#### *public* next()(PHP 5 &gt;= 5.0.0)<br/>
Move forward to next element
 * `link`  http://php.net/manual/en/iterator.next.php
 * `return`  void Any returned value is ignored.
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L180)

#### *public* key#### *public* key()(PHP 5 &gt;= 5.0.0)<br/>
Return the key of the current element
 * `link`  http://php.net/manual/en/iterator.key.php
 * `return`  mixed scalar on success, or null on failure.
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L191)

#### *public* valid#### *public* valid()(PHP 5 &gt;= 5.0.0)<br/>
Checks if current position is valid
 * `link`  http://php.net/manual/en/iterator.valid.php
 * `return`  boolean The return value will be casted to boolean and then evaluated.
Returns true on success or false on failure.
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L208)

#### *public* rewind#### *public* rewind()(PHP 5 &gt;= 5.0.0)<br/>
Rewind the Iterator to the first element
 * `link`  http://php.net/manual/en/iterator.rewind.php
 * `return`  void Any returned value is ignored.
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L227)

#### *public* jsonSerialize#### *public* jsonSerialize()(PHP 5 >= 5.4.0)
Serializes the object to a value that can be serialized natively by json_encode().
 * `link`  http://docs.php.net/manual/en/jsonserializable.jsonserialize.php
 * `return`  mixed Returns data which can be serialized by json_encode(), which is a value of any type other than a resource.
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Maybe.php#L241)
