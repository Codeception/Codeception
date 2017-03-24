<?php

namespace Codeception\PHPUnit;

/**
 * Documentation of all assertXXX methods except assert(), assertNot() and assertThatItsNot()
 * where copied from \PHPUnit_Framework_Assert.
 */
class AssertWrapper
{
    protected function assert($arguments, $not = false)
    {
        $not    = $not ? 'Not' : '';
        $method = ucfirst(array_shift($arguments));
        if (($method === 'True') && $not) {
            $method = 'False';
            $not    = '';
        }
        if (($method === 'False') && $not) {
            $method = 'True';
            $not    = '';
        }

        call_user_func_array(array('\PHPUnit_Framework_Assert', 'assert' . $not . $method), $arguments);
    }

    protected function assertNot($arguments)
    {
        $this->assert($arguments, true);
    }

    /**
     * Asserts that an array has a specified key.
     *
     * @param  mixed  $key
     * @param  array|ArrayAccess  $array
     * @param  string $message
     */
    protected function assertArrayHasKey($key, $array, $message = '')
    {
        \PHPUnit_Framework_Assert::assertArrayHasKey($key, $array, $message);
    }

    /**
     * Asserts that an array does not have a specified key.
     *
     * @param  mixed  $key
     * @param  array|ArrayAccess  $array
     * @param  string $message
     */
    protected function assertArrayNotHasKey($key, $array, $message = '')
    {
        \PHPUnit_Framework_Assert::assertArrayNotHasKey($key, $array, $message);
    }

    /**
     * Asserts that a haystack contains a needle.
     *
     * @param  mixed   $needle
     * @param  mixed   $haystack
     * @param  string  $message
     * @param  boolean $ignoreCase
     * @param  boolean $checkForObjectIdentity
     */
    protected function assertContains($needle, $haystack, $message = '', $ignoreCase = FALSE, $checkForObjectIdentity = TRUE)
    {
        \PHPUnit_Framework_Assert::assertContains($needle, $haystack, $message, $ignoreCase, $checkForObjectIdentity);
    }

    /**
     * Asserts that a haystack that is stored in a static attribute of a class
     * or an attribute of an object contains a needle.
     *
     * @param  mixed   $needle
     * @param  string  $haystackAttributeName
     * @param  mixed   $haystackClassOrObject
     * @param  string  $message
     * @param  boolean $ignoreCase
     * @param  boolean $checkForObjectIdentity
     */
    protected function assertAttributeContains($needle, $haystackAttributeName, $haystackClassOrObject, $message = '', $ignoreCase = FALSE, $checkForObjectIdentity = TRUE)
    {
        \PHPUnit_Framework_Assert::assertAttributeContains($needle, $haystackAttributeName, $haystackClassOrObject, $message, $ignoreCase, $checkForObjectIdentity);
    }

    /**
     * Asserts that a haystack does not contain a needle.
     *
     * @param  mixed   $needle
     * @param  mixed   $haystack
     * @param  string  $message
     * @param  boolean $ignoreCase
     * @param  boolean $checkForObjectIdentity
     */
    protected function assertNotContains($needle, $haystack, $message = '', $ignoreCase = FALSE, $checkForObjectIdentity = TRUE)
    {
        \PHPUnit_Framework_Assert::assertNotContains($needle, $haystack, $message, $ignoreCase, $checkForObjectIdentity);
    }

    /**
     * Asserts that a haystack that is stored in a static attribute of a class
     * or an attribute of an object does not contain a needle.
     *
     * @param  mixed   $needle
     * @param  string  $haystackAttributeName
     * @param  mixed   $haystackClassOrObject
     * @param  string  $message
     * @param  boolean $ignoreCase
     * @param  boolean $checkForObjectIdentity
     */
    protected function assertAttributeNotContains($needle, $haystackAttributeName, $haystackClassOrObject, $message = '', $ignoreCase = FALSE, $checkForObjectIdentity = TRUE)
    {
        \PHPUnit_Framework_Assert::assertAttributeNotContains($needle, $haystackAttributeName, $haystackClassOrObject, $message, $ignoreCase, $checkForObjectIdentity);
    }

    /**
     * Asserts that a haystack contains only values of a given type.
     *
     * @param  string  $type
     * @param  mixed   $haystack
     * @param  boolean $isNativeType
     * @param  string  $message
     */
    protected function assertContainsOnly($type, $haystack, $isNativeType = NULL, $message = '')
    {
        \PHPUnit_Framework_Assert::assertContainsOnly($type, $haystack, $isNativeType, $message);
    }

    /**
     * Asserts that a haystack contains only instances of a given classname
     *
     * @param string $classname
     * @param array|Traversable $haystack
     * @param string $message
     */
    protected function assertContainsOnlyInstancesOf($classname, $haystack, $message = '')
    {
        \PHPUnit_Framework_Assert::assertContainsOnlyInstancesOf($classname, $haystack, $message);
    }

    /**
     * Asserts that a haystack that is stored in a static attribute of a class
     * or an attribute of an object contains only values of a given type.
     *
     * @param  string  $type
     * @param  string  $haystackAttributeName
     * @param  mixed   $haystackClassOrObject
     * @param  boolean $isNativeType
     * @param  string  $message
     */
    protected function assertAttributeContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType = NULL, $message = '')
    {
        \PHPUnit_Framework_Assert::assertAttributeContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType, $message);
    }

    /**
     * Asserts that a haystack does not contain only values of a given type.
     *
     * @param  string  $type
     * @param  mixed   $haystack
     * @param  boolean $isNativeType
     * @param  string  $message
     */
    protected function assertNotContainsOnly($type, $haystack, $isNativeType = NULL, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotContainsOnly($type, $haystack, $isNativeType, $message);
    }

    /**
     * Asserts that a haystack that is stored in a static attribute of a class
     * or an attribute of an object does not contain only values of a given
     * type.
     *
     * @param  string  $type
     * @param  string  $haystackAttributeName
     * @param  mixed   $haystackClassOrObject
     * @param  boolean $isNativeType
     * @param  string  $message
     */
    protected function assertAttributeNotContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType = NULL, $message = '')
    {
        \PHPUnit_Framework_Assert::assertAttributeNotContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType, $message);
    }

    /**
     * Asserts the number of elements of an array, Countable or Iterator.
     *
     * @param integer $expectedCount
     * @param mixed   $haystack
     * @param string  $message
     */
    protected function assertCount($expectedCount, $haystack, $message = '')
    {
        \PHPUnit_Framework_Assert::assertCount($expectedCount, $haystack, $message);
    }

    /**
     * Asserts the number of elements of an array, Countable or Iterator
     * that is stored in an attribute.
     *
     * @param integer $expectedCount
     * @param string  $haystackAttributeName
     * @param mixed   $haystackClassOrObject
     * @param string  $message
     */
    protected function assertAttributeCount($expectedCount, $haystackAttributeName, $haystackClassOrObject, $message = '')
    {
        \PHPUnit_Framework_Assert::assertAttributeCount($expectedCount, $haystackAttributeName, $haystackClassOrObject, $message);
    }

    /**
     * Asserts the number of elements of an array, Countable or Iterator.
     *
     * @param integer $expectedCount
     * @param mixed   $haystack
     * @param string  $message
     */
    protected function assertNotCount($expectedCount, $haystack, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotCount($expectedCount, $haystack, $message);
    }

    /**
     * Asserts the number of elements of an array, Countable or Iterator
     * that is stored in an attribute.
     *
     * @param integer $expectedCount
     * @param string  $haystackAttributeName
     * @param mixed   $haystackClassOrObject
     * @param string  $message
     */
    protected function assertAttributeNotCount($expectedCount, $haystackAttributeName, $haystackClassOrObject, $message = '')
    {
        \PHPUnit_Framework_Assert::assertAttributeNotCount($expectedCount, $haystackAttributeName, $haystackClassOrObject, $message);
    }

    /**
     * Asserts that two variables are equal.
     *
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     * @param  float   $delta
     * @param  integer $maxDepth
     * @param  boolean $canonicalize
     * @param  boolean $ignoreCase
     */
    protected function assertEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        \PHPUnit_Framework_Assert::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    /**
     * Asserts that a variable is equal to an attribute of an object.
     *
     * @param  mixed   $expected
     * @param  string  $actualAttributeName
     * @param  string  $actualClassOrObject
     * @param  string  $message
     * @param  float   $delta
     * @param  integer $maxDepth
     * @param  boolean $canonicalize
     * @param  boolean $ignoreCase
     */
    protected function assertAttributeEquals($expected, $actualAttributeName, $actualClassOrObject, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        \PHPUnit_Framework_Assert::assertAttributeEquals($expected, $actualAttributeName, $actualClassOrObject, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    /**
     * Asserts that two variables are not equal.
     *
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     * @param  float   $delta
     * @param  integer $maxDepth
     * @param  boolean $canonicalize
     * @param  boolean $ignoreCase
     */
    protected function assertNotEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        \PHPUnit_Framework_Assert::assertNotEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    /**
     * Asserts that a variable is not equal to an attribute of an object.
     *
     * @param  mixed   $expected
     * @param  string  $actualAttributeName
     * @param  string  $actualClassOrObject
     * @param  string  $message
     * @param  float   $delta
     * @param  integer $maxDepth
     * @param  boolean $canonicalize
     * @param  boolean $ignoreCase
     */
    protected function assertAttributeNotEquals($expected, $actualAttributeName, $actualClassOrObject, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        \PHPUnit_Framework_Assert::assertAttributeNotEquals($expected, $actualAttributeName, $actualClassOrObject, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    /**
     * Asserts that a variable is empty.
     *
     * @param  mixed   $actual
     * @param  string  $message
     */
    protected function assertEmpty($actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertEmpty($actual, $message);
    }

    /**
     * Asserts that a static attribute of a class or an attribute of an object
     * is empty.
     *
     * @param string $haystackAttributeName
     * @param mixed  $haystackClassOrObject
     * @param string $message
     */
    protected function assertAttributeEmpty($haystackAttributeName, $haystackClassOrObject, $message = '')
    {
        \PHPUnit_Framework_Assert::assertAttributeEmpty($haystackAttributeName, $haystackClassOrObject, $message);
    }

    /**
     * Asserts that a variable is not empty.
     *
     * @param  mixed   $actual
     * @param  string  $message
     */
    protected function assertNotEmpty($actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotEmpty($actual, $message);
    }

    /**
     * Asserts that a static attribute of a class or an attribute of an object
     * is not empty.
     *
     * @param string $haystackAttributeName
     * @param mixed  $haystackClassOrObject
     * @param string $message
     */
    protected function assertAttributeNotEmpty($haystackAttributeName, $haystackClassOrObject, $message = '')
    {
        \PHPUnit_Framework_Assert::assertAttributeNotEmpty($haystackAttributeName, $haystackClassOrObject, $message);
    }

    /**
     * Asserts that a value is greater than another value.
     *
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     */
    protected function assertGreaterThan($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertGreaterThan($expected, $actual, $message);
    }

    /**
     * Asserts that an attribute is greater than another value.
     *
     * @param  mixed   $expected
     * @param  string  $actualAttributeName
     * @param  string  $actualClassOrObject
     * @param  string  $message
     */
    protected function assertAttributeGreaterThan($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        \PHPUnit_Framework_Assert::assertAttributeGreaterThan($expected, $haystackAttributeName, $haystackClassOrObject, $message);
    }

    /**
     * Asserts that a value is greater than or equal to another value.
     *
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     */
    protected function assertGreaterThanOrEqual($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertGreaterThanOrEqual($expected, $actual, $message);
    }

    /**
     * Asserts that an attribute is greater than or equal to another value.
     *
     * @param  mixed   $expected
     * @param  string  $actualAttributeName
     * @param  string  $actualClassOrObject
     * @param  string  $message
     */
    protected function assertAttributeGreaterThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        \PHPUnit_Framework_Assert::assertAttributeGreaterThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message);
    }

    /**
     * Asserts that a value is smaller than another value.
     *
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     */
    protected function assertLessThan($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertLessThan($expected, $actual, $message);
    }

    /**
     * Asserts that an attribute is smaller than another value.
     *
     * @param  mixed   $expected
     * @param  string  $actualAttributeName
     * @param  string  $actualClassOrObject
     * @param  string  $message
     */
    protected function assertAttributeLessThan($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        \PHPUnit_Framework_Assert::assertAttributeLessThan($expected, $actualAttributeName, $actualClassOrObject, $message);
    }

    /**
     * Asserts that a value is smaller than or equal to another value.
     *
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     */
    protected function assertLessThanOrEqual($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertLessThanOrEqual($expected, $actual, $message);
    }

    /**
     * Asserts that an attribute is smaller than or equal to another value.
     *
     * @param  mixed   $expected
     * @param  string  $actualAttributeName
     * @param  string  $actualClassOrObject
     * @param  string  $message
     */
    protected function assertAttributeLessThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        \PHPUnit_Framework_Assert::assertAttributeLessThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message);
    }

    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file.
     *
     * @param  string  $expected
     * @param  string  $actual
     * @param  string  $message
     * @param  boolean $canonicalize
     * @param  boolean $ignoreCase
     */
    protected function assertFileEquals($expected, $actual, $message = '', $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        \PHPUnit_Framework_Assert::assertFileEquals($expected, $actual, $message, $canonicalize, $ignoreCase);
    }

    /**
     * Asserts that the contents of one file is not equal to the contents of
     * another file.
     *
     * @param  string  $expected
     * @param  string  $actual
     * @param  string  $message
     * @param  boolean $canonicalize
     * @param  boolean $ignoreCase
     */
    protected function assertFileNotEquals($expected, $actual, $message = '', $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        \PHPUnit_Framework_Assert::assertFileNotEquals($expected, $actual, $message, $canonicalize, $ignoreCase);
    }

    /**
     * Asserts that the contents of a string is equal
     * to the contents of a file.
     *
     * @param  string  $expectedFile
     * @param  string  $actualString
     * @param  string  $message
     * @param  boolean $canonicalize
     * @param  boolean $ignoreCase
     */
    protected function assertStringEqualsFile($expectedFile, $actualString, $message = '', $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        \PHPUnit_Framework_Assert::assertStringEqualsFile($expectedFile, $actualString, $message, $canonicalize, $ignoreCase);
    }

    /**
     * Asserts that the contents of a string is not equal
     * to the contents of a file.
     *
     * @param  string  $expectedFile
     * @param  string  $actualString
     * @param  string  $message
     * @param  boolean $canonicalize
     * @param  boolean $ignoreCase
     */
    protected function assertStringNotEqualsFile($expectedFile, $actualString, $message = '', $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        \PHPUnit_Framework_Assert::assertStringNotEqualsFile($expectedFile, $actualString, $message, $canonicalize, $ignoreCase);
    }

    /**
     * Asserts that a file exists.
     *
     * @param  string $filename
     * @param  string $message
     */
    protected function assertFileExists($filename, $message = '')
    {
        \PHPUnit_Framework_Assert::assertFileExists($filename, $message);
    }

    /**
     * Asserts that a file does not exist.
     *
     * @param  string $filename
     * @param  string $message
     */
    protected function assertFileNotExists($filename, $message = '')
    {
        \PHPUnit_Framework_Assert::assertFileNotExists($filename, $message);
    }

    /**
     * Asserts that a condition is true.
     *
     * @param  boolean $condition
     * @param  string  $message
     */
    protected function assertTrue($condition, $message = '')
    {
        \PHPUnit_Framework_Assert::assertTrue($condition, $message);
    }

    /**
     * Asserts that a condition is false.
     *
     * @param  boolean  $condition
     * @param  string   $message
     */
    protected function assertFalse($condition, $message = '')
    {
        \PHPUnit_Framework_Assert::assertFalse($condition, $message);
    }

    /**
     * Asserts that a variable is not NULL.
     *
     * @param  mixed  $actual
     * @param  string $message
     */
    protected function assertNotNull($actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotNull($actual, $message);
    }

    /**
     * Asserts that a variable is NULL.
     *
     * @param  mixed  $actual
     * @param  string $message
     */
    protected function assertNull($actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNull($actual, $message);
    }

    /**
     * Asserts that a class has a specified attribute.
     *
     * @param  string $attributeName
     * @param  string $className
     * @param  string $message
     */
    protected function assertClassHasAttribute($attributeName, $className, $message = '')
    {
        \PHPUnit_Framework_Assert::assertClassHasAttribute($attributeName, $className, $message);
    }

    /**
     * Asserts that a class does not have a specified attribute.
     *
     * @param  string $attributeName
     * @param  string $className
     * @param  string $message
     */
    protected function assertClassNotHasAttribute($attributeName, $className, $message = '')
    {
        \PHPUnit_Framework_Assert::assertClassNotHasAttribute($attributeName, $className, $message);
    }

    /**
     * Asserts that a class has a specified static attribute.
     *
     * @param  string $attributeName
     * @param  string $className
     * @param  string $message
     */
    protected function assertClassHasStaticAttribute($attributeName, $className, $message = '')
    {
        \PHPUnit_Framework_Assert::assertClassHasStaticAttribute($attributeName, $className, $message);
    }

    /**
     * Asserts that a class does not have a specified static attribute.
     *
     * @param  string $attributeName
     * @param  string $className
     * @param  string $message
     */
    protected function assertClassNotHasStaticAttribute($attributeName, $className, $message = '')
    {
        \PHPUnit_Framework_Assert::assertClassNotHasStaticAttribute($attributeName, $className, $message);
    }

    /**
     * Asserts that an object has a specified attribute.
     *
     * @param  string $attributeName
     * @param  object $object
     * @param  string $message
     */
    protected function assertObjectHasAttribute($attributeName, $object, $message = '')
    {
        \PHPUnit_Framework_Assert::assertObjectHasAttribute($attributeName, $object, $message);
    }

    /**
     * Asserts that an object does not have a specified attribute.
     *
     * @param  string $attributeName
     * @param  object $object
     * @param  string $message
     */
    protected function assertObjectNotHasAttribute($attributeName, $object, $message = '')
    {
        \PHPUnit_Framework_Assert::assertObjectNotHasAttribute($attributeName, $object, $message);
    }

    /**
     * Asserts that two variables have the same type and value.
     * Used on objects, it asserts that two variables reference
     * the same object.
     *
     * @param  mixed  $expected
     * @param  mixed  $actual
     * @param  string $message
     */
    protected function assertSame($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertSame($expected, $actual, $message);
    }

    /**
     * Asserts that a variable and an attribute of an object have the same type
     * and value.
     *
     * @param  mixed  $expected
     * @param  string $actualAttributeName
     * @param  object $actualClassOrObject
     * @param  string $message
     */
    protected function assertAttributeSame($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        \PHPUnit_Framework_Assert::assertAttributeSame($expected, $actualAttributeName, $actualClassOrObject, $message);
    }

    /**
     * Asserts that two variables do not have the same type and value.
     * Used on objects, it asserts that two variables do not reference
     * the same object.
     *
     * @param  mixed  $expected
     * @param  mixed  $actual
     * @param  string $message
     */
    protected function assertNotSame($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotSame($expected, $actual, $message);
    }

    /**
     * Asserts that a variable and an attribute of an object do not have the
     * same type and value.
     *
     * @param  mixed  $expected
     * @param  string $actualAttributeName
     * @param  object $actualClassOrObject
     * @param  string $message
     */
    protected function assertAttributeNotSame($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        \PHPUnit_Framework_Assert::assertAttributeNotSame($expected, $actualAttributeName, $actualClassOrObject, $message);
    }

    /**
     * Asserts that a variable is of a given type.
     *
     * @param string $expected
     * @param mixed  $actual
     * @param string $message
     */
    protected function assertInstanceOf($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertInstanceOf($expected, $actual, $message);
    }

    /**
     * Asserts that an attribute is of a given type.
     *
     * @param string $expected
     * @param string $attributeName
     * @param mixed  $classOrObject
     * @param string $message
     */
    protected function assertAttributeInstanceOf($expected, $attributeName, $classOrObject, $message = '')
    {
        \PHPUnit_Framework_Assert::assertAttributeInstanceOf($expected, $attributeName, $classOrObject, $message);
    }

    /**
     * Asserts that a variable is not of a given type.
     *
     * @param string $expected
     * @param mixed  $actual
     * @param string $message
     */
    protected function assertNotInstanceOf($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotInstanceOf($expected, $actual, $message);
    }

    /**
     * Asserts that an attribute is of a given type.
     *
     * @param string $expected
     * @param string $attributeName
     * @param mixed  $classOrObject
     * @param string $message
     */
    protected function assertAttributeNotInstanceOf($expected, $attributeName, $classOrObject, $message = '')
    {
        \PHPUnit_Framework_Assert::assertAttributeNotInstanceOf($expected, $attributeName, $classOrObject, $message);
    }

    /**
     * Asserts that a variable is of a given type.
     *
     * @param string $expected
     * @param mixed  $actual
     * @param string $message
     */
    protected function assertInternalType($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertInternalType($expected, $actual, $message);
    }

    /**
     * Asserts that an attribute is of a given type.
     *
     * @param string $expected
     * @param string $attributeName
     * @param mixed  $classOrObject
     * @param string $message
     */
    protected function assertAttributeInternalType($expected, $attributeName, $classOrObject, $message = '')
    {
        \PHPUnit_Framework_Assert::assertAttributeInternalType($expected, $attributeName, $classOrObject, $message);
    }

    /**
     * Asserts that a variable is not of a given type.
     *
     * @param string $expected
     * @param mixed  $actual
     * @param string $message
     */
    protected function assertNotInternalType($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotInternalType($expected, $actual, $message);
    }

    /**
     * Asserts that an attribute is of a given type.
     *
     * @param string $expected
     * @param string $attributeName
     * @param mixed  $classOrObject
     * @param string $message
     */
    protected function assertAttributeNotInternalType($expected, $attributeName, $classOrObject, $message = '')
    {
        \PHPUnit_Framework_Assert::assertAttributeNotInternalType($expected, $attributeName, $classOrObject, $message);
    }

    /**
     * Asserts that a string matches a given regular expression.
     *
     * @param  string $pattern
     * @param  string $string
     * @param  string $message
     */
    protected function assertRegExp($pattern, $string, $message = '')
    {
        \PHPUnit_Framework_Assert::assertRegExp($pattern, $string, $message);
    }

    /**
     * Asserts that a string does not match a given regular expression.
     *
     * @param  string $pattern
     * @param  string $string
     * @param  string $message
     */
    protected function assertNotRegExp($pattern, $string, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotRegExp($pattern, $string, $message);
    }

    /**
     * Assert that the size of two arrays (or `Countable` or `Iterator` objects)
     * is the same.
     *
     * @param array|Countable|Iterator $expected
     * @param array|Countable|Iterator $actual
     * @param string $message
     */
    protected function assertSameSize($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertSameSize($expected, $actual, $message);
    }

    /**
     * Assert that the size of two arrays (or `Countable` or `Iterator` objects)
     * is not the same.
     *
     * @param array|Countable|Iterator $expected
     * @param array|Countable|Iterator $actual
     * @param string $message
     */
    protected function assertNotSameSize($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotSameSize($expected, $actual, $message);
    }

    /**
     * Asserts that a string matches a given format string.
     *
     * @param  string $format
     * @param  string $string
     * @param  string $message
     */
    protected function assertStringMatchesFormat($format, $string, $message = '')
    {
        \PHPUnit_Framework_Assert::assertStringMatchesFormat($format, $string, $message);
    }

    /**
     * Asserts that a string does not match a given format string.
     *
     * @param  string $format
     * @param  string $string
     * @param  string $message
     */
    protected function assertStringNotMatchesFormat($format, $string, $message = '')
    {
        \PHPUnit_Framework_Assert::assertStringNotMatchesFormat($format, $string, $message);
    }

    /**
     * Asserts that a string matches a given format file.
     *
     * @param  string $formatFile
     * @param  string $string
     * @param  string $message
     */
    protected function assertStringMatchesFormatFile($formatFile, $string, $message = '')
    {
        \PHPUnit_Framework_Assert::assertStringMatchesFormatFile($formatFile, $string, $message);
    }

    /**
     * Asserts that a string does not match a given format string.
     *
     * @param  string $formatFile
     * @param  string $string
     * @param  string $message
     */
    protected function assertStringNotMatchesFormatFile($formatFile, $string, $message = '')
    {
        \PHPUnit_Framework_Assert::assertStringNotMatchesFormatFile($formatFile, $string, $message);
    }

    /**
     * Asserts that a string starts with a given prefix.
     *
     * @param  string $prefix
     * @param  string $string
     * @param  string $message
     */
    protected function assertStringStartsWith($prefix, $string, $message = '')
    {
        \PHPUnit_Framework_Assert::assertStringStartsWith($prefix, $string, $message);
    }

    /**
     * Asserts that a string starts not with a given prefix.
     *
     * @param  string $prefix
     * @param  string $string
     * @param  string $message
     */
    protected function assertStringStartsNotWith($prefix, $string, $message = '')
    {
        \PHPUnit_Framework_Assert::assertStringStartsNotWith($prefix, $string, $message);
    }

    /**
     * Asserts that a string ends with a given suffix.
     *
     * @param  string $suffix
     * @param  string $string
     * @param  string $message
     */
    protected function assertStringEndsWith($suffix, $string, $message = '')
    {
        \PHPUnit_Framework_Assert::assertStringEndsWith($suffix, $string, $message);
    }

    /**
     * Asserts that a string ends not with a given suffix.
     *
     * @param  string $suffix
     * @param  string $string
     * @param  string $message
     */
    protected function assertStringEndsNotWith($suffix, $string, $message = '')
    {
        \PHPUnit_Framework_Assert::assertStringEndsNotWith($suffix, $string, $message);
    }

    /**
     * Asserts that two XML files are equal.
     *
     * @param  string $expectedFile
     * @param  string $actualFile
     * @param  string $message
     */
    protected function assertXmlFileEqualsXmlFile($expectedFile, $actualFile, $message = '')
    {
        \PHPUnit_Framework_Assert::assertXmlFileEqualsXmlFile($expectedFile, $actualFile, $message);
    }

    /**
     * Asserts that two XML files are not equal.
     *
     * @param  string $expectedFile
     * @param  string $actualFile
     * @param  string $message
     */
    protected function assertXmlFileNotEqualsXmlFile($expectedFile, $actualFile, $message = '')
    {
        \PHPUnit_Framework_Assert::assertXmlFileNotEqualsXmlFile($expectedFile, $actualFile, $message);
    }

    /**
     * Asserts that two XML documents are equal.
     *
     * @param  string $expectedFile
     * @param  string $actualXml
     * @param  string $message
     */
    protected function assertXmlStringEqualsXmlFile($expectedFile, $actualXml, $message = '')
    {
        \PHPUnit_Framework_Assert::assertXmlStringEqualsXmlFile($expectedFile, $actualXml, $message);
    }

    /**
     * Asserts that two XML documents are not equal.
     *
     * @param  string $expectedFile
     * @param  string $actualXml
     * @param  string $message
     */
    protected function assertXmlStringNotEqualsXmlFile($expectedFile, $actualXml, $message = '')
    {
        \PHPUnit_Framework_Assert::assertXmlStringNotEqualsXmlFile($expectedFile, $actualXml, $message);
    }

    /**
     * Asserts that two XML documents are equal.
     *
     * @param  string $expectedXml
     * @param  string $actualXml
     * @param  string $message
     */
    protected function assertXmlStringEqualsXmlString($expectedXml, $actualXml, $message = '')
    {
        \PHPUnit_Framework_Assert::assertXmlStringEqualsXmlString($expectedXml, $actualXml, $message);
    }

    /**
     * Asserts that two XML documents are not equal.
     *
     * @param  string $expectedXml
     * @param  string $actualXml
     * @param  string $message
     */
    protected function assertXmlStringNotEqualsXmlString($expectedXml, $actualXml, $message = '')
    {
        \PHPUnit_Framework_Assert::assertXmlStringNotEqualsXmlString($expectedXml, $actualXml, $message);
    }

    /**
     * Asserts that a hierarchy of DOMElements matches.
     *
     * @param DOMElement $expectedElement
     * @param DOMElement $actualElement
     * @param boolean $checkAttributes
     * @param string  $message
     */
    protected function assertEqualXMLStructure(DOMElement $expectedElement, DOMElement $actualElement, $checkAttributes = FALSE, $message = '')
    {
        \PHPUnit_Framework_Assert::assertEqualXMLStructure($expectedElement, $actualElement, $checkAttributes, $message);
    }

    /**
     * Assert the presence, absence, or count of elements in a document matching
     * the CSS $selector, regardless of the contents of those elements.
     *
     * The first argument, $selector, is the CSS selector used to match
     * the elements in the $actual document.
     *
     * The second argument, $count, can be either boolean or numeric.
     * When boolean, it asserts for presence of elements matching the selector
     * (TRUE) or absence of elements (FALSE).
     * When numeric, it asserts the count of elements.
     *
     * assertSelectCount("#binder", true, $xml);  // any?
     * assertSelectCount(".binder", 3, $xml);     // exactly 3?
     *
     * @param  array   $selector
     * @param  integer $count
     * @param  mixed   $actual
     * @param  string  $message
     * @param  boolean $isHtml
     */
    protected function assertSelectCount($selector, $count, $actual, $message = '', $isHtml = TRUE)
    {
        \PHPUnit_Framework_Assert::assertSelectCount($expected, $count, $actual, $message, $isHtml);
    }

    /**
     * assertSelectRegExp("#binder .name", "/Mike|Derek/", true, $xml); // any?
     * assertSelectRegExp("#binder .name", "/Mike|Derek/", 3, $xml);    // 3?
     *
     * @param  array   $selector
     * @param  string  $pattern
     * @param  integer $count
     * @param  mixed   $actual
     * @param  string  $message
     * @param  boolean $isHtml
     */
    protected function assertSelectRegExp($selector, $pattern, $count, $actual, $message = '', $isHtml = TRUE)
    {
        \PHPUnit_Framework_Assert::assertSelectRegExp($selector, $pattern, $count, $actual, $message, $isHtml);
    }

    /**
     * assertSelectEquals("#binder .name", "Chuck", true,  $xml);  // any?
     * assertSelectEquals("#binder .name", "Chuck", false, $xml);  // none?
     *
     * @param  array   $selector
     * @param  string  $content
     * @param  integer $count
     * @param  mixed   $actual
     * @param  string  $message
     * @param  boolean $isHtml
     */
    protected function assertSelectEquals($selector, $content, $count, $actual, $message = '', $isHtml = TRUE)
    {
        \PHPUnit_Framework_Assert::assertSelectEquals($selector, $pattern, $count, $actual, $message, $isHtml);
    }

    /**
     * Evaluate an HTML or XML string and assert its structure and/or contents.
     *
     * The first argument ($matcher) is an associative array that specifies the
     * match criteria for the assertion:
     *
     *  - `id`           : the node with the given id attribute must match the
     *                     corresponsing value.
     *  - `tag`          : the node type must match the corresponding value.
     *  - `attributes`   : a hash. The node's attributres must match the
     *                     corresponsing values in the hash.
     *  - `content`      : The text content must match the given value.
     *  - `parent`       : a hash. The node's parent must match the
     *                     corresponsing hash.
     *  - `child`        : a hash. At least one of the node's immediate children
     *                     must meet the criteria described by the hash.
     *  - `ancestor`     : a hash. At least one of the node's ancestors must
     *                     meet the criteria described by the hash.
     *  - `descendant`   : a hash. At least one of the node's descendants must
     *                     meet the criteria described by the hash.
     *  - `children`     : a hash, for counting children of a node.
     *                     Accepts the keys:
     *    - `count`        : a number which must equal the number of children
     *                       that match
     *    - `less_than`    : the number of matching children must be greater
     *                       than this number
     *    - `greater_than` : the number of matching children must be less than
     *                       this number
     *    - `only`         : another hash consisting of the keys to use to match
     *                       on the children, and only matching children will be
     *                       counted
     *
     * <code>
     * // Matcher that asserts that there is an element with an id="my_id".
     * $matcher = array('id' => 'my_id');
     *
     * // Matcher that asserts that there is a "span" tag.
     * $matcher = array('tag' => 'span');
     *
     * // Matcher that asserts that there is a "span" tag with the content
     * // "Hello World".
     * $matcher = array('tag' => 'span', 'content' => 'Hello World');
     *
     * // Matcher that asserts that there is a "span" tag with content matching
     * // the regular expression pattern.
     * $matcher = array('tag' => 'span', 'content' => 'regexp:/Try P(HP|ython)/');
     *
     * // Matcher that asserts that there is a "span" with an "list" class
     * // attribute.
     * $matcher = array(
     *   'tag'        => 'span',
     *   'attributes' => array('class' => 'list')
     * );
     *
     * // Matcher that asserts that there is a "span" inside of a "div".
     * $matcher = array(
     *   'tag'    => 'span',
     *   'parent' => array('tag' => 'div')
     * );
     *
     * // Matcher that asserts that there is a "span" somewhere inside a
     * // "table".
     * $matcher = array(
     *   'tag'      => 'span',
     *   'ancestor' => array('tag' => 'table')
     * );
     *
     * // Matcher that asserts that there is a "span" with at least one "em"
     * // child.
     * $matcher = array(
     *   'tag'   => 'span',
     *   'child' => array('tag' => 'em')
     * );
     *
     * // Matcher that asserts that there is a "span" containing a (possibly
     * // nested) "strong" tag.
     * $matcher = array(
     *   'tag'        => 'span',
     *   'descendant' => array('tag' => 'strong')
     * );
     *
     * // Matcher that asserts that there is a "span" containing 5-10 "em" tags
     * // as immediate children.
     * $matcher = array(
     *   'tag'      => 'span',
     *   'children' => array(
     *     'less_than'    => 11,
     *     'greater_than' => 4,
     *     'only'         => array('tag' => 'em')
     *   )
     * );
     *
     * // Matcher that asserts that there is a "div", with an "ul" ancestor and
     * // a "li" parent (with class="enum"), and containing a "span" descendant
     * // that contains an element with id="my_test" and the text "Hello World".
     * $matcher = array(
     *   'tag'        => 'div',
     *   'ancestor'   => array('tag' => 'ul'),
     *   'parent'     => array(
     *     'tag'        => 'li',
     *     'attributes' => array('class' => 'enum')
     *   ),
     *   'descendant' => array(
     *     'tag'   => 'span',
     *     'child' => array(
     *       'id'      => 'my_test',
     *       'content' => 'Hello World'
     *     )
     *   )
     * );
     *
     * // Use assertTag() to apply a $matcher to a piece of $html.
     * $this->assertTag($matcher, $html);
     *
     * // Use assertTag() to apply a $matcher to a piece of $xml.
     * $this->assertTag($matcher, $xml, '', FALSE);
     * </code>
     *
     * The second argument ($actual) is a string containing either HTML or
     * XML text to be tested.
     *
     * The third argument ($message) is an optional message that will be
     * used if the assertion fails.
     *
     * The fourth argument ($html) is an optional flag specifying whether
     * to load the $actual string into a DOMDocument using the HTML or
     * XML load strategy.  It is TRUE by default, which assumes the HTML
     * load strategy.  In many cases, this will be acceptable for XML as well.
     *
     * @param  array   $matcher
     * @param  string  $actual
     * @param  string  $message
     * @param  boolean $isHtml
     */
    protected function assertTag($matcher, $actual, $message = '', $isHtml = TRUE)
    {
        \PHPUnit_Framework_Assert::assertTag($matcher, $actual, $message, $isHtml);
    }

    /**
     * This assertion is the exact opposite of assertTag().
     *
     * Rather than asserting that $matcher results in a match, it asserts that
     * $matcher does not match.
     *
     * @param  array   $matcher
     * @param  string  $actual
     * @param  string  $message
     * @param  boolean $isHtml
     */
    protected function assertNotTag($matcher, $actual, $message = '', $isHtml = TRUE)
    {
        \PHPUnit_Framework_Assert::assertNotTag($matcher, $actual, $message, $isHtml);
    }

    /**
     * Evaluates a PHPUnit_Framework_Constraint matcher object.
     *
     * @param  mixed                        $value
     * @param  PHPUnit_Framework_Constraint $constraint
     * @param  string                       $message
     */
    protected function assertThat($value, PHPUnit_Framework_Constraint $constraint, $message = '')
    {
        \PHPUnit_Framework_Assert::assertThat($value, $constraint, $message);
    }

    protected function assertThatItsNot($haystack, $constraint, $message)
    {
        $constraint = new \PHPUnit_Framework_Constraint_Not($constraint);
        \PHPUnit_Framework_Assert::assertThat($haystack, $constraint, $message);
    }

    /**
     * Asserts that a string is a valid JSON string.
     *
     * @param  string $filename
     * @param  string $message
     * @since  Method available since Release 3.7.20
     */
    protected function assertJson($expectedJson, $message = '')
    {
        \PHPUnit_Framework_Assert::assertJson($expectedJson, $message);
    }

    /**
     * Asserts that two given JSON encoded objects or arrays are equal.
     *
     * @param string $expectedJson
     * @param string $actualJson
     * @param string $message
     */
    protected function assertJsonStringEqualsJsonString($expectedJson, $actualJson, $message = '')
    {
        \PHPUnit_Framework_Assert::assertJsonStringEqualsJsonString($expectedJson, $actualJson, $message);
    }

    /**
     * Asserts that two given JSON encoded objects or arrays are not equal.
     *
     * @param string $expectedJson
     * @param string $actualJson
     * @param string $message
     */
    protected function assertJsonStringNotEqualsJsonString($expectedJson, $actualJson, $message = '')
    {
        \PHPUnit_Framework_Assert::assertJsonStringNotEqualsJsonString($expectedJson, $actualJson, $message);
    }

    /**
     * Asserts that the generated JSON encoded object and the content of the given file are equal.
     *
     * @param string $expectedFile
     * @param string $actualJson
     * @param string $message
     */
    protected function assertJsonStringEqualsJsonFile($expectedFile, $actualJson, $message = '')
    {
        \PHPUnit_Framework_Assert::assertJsonStringEqualsJsonFile($expectedFile, $actualJson, $message);
    }

    /**
     * Asserts that the generated JSON encoded object and the content of the given file are not equal.
     *
     * @param string $expectedFile
     * @param string $actualJson
     * @param string $message
     */
    protected function assertJsonStringNotEqualsJsonFile($expectedFile, $actualJson, $message = '')
    {
        \PHPUnit_Framework_Assert::assertJsonStringNotEqualsJsonFile($expectedFile, $actualJson, $message);
    }

    /**
     * Asserts that two JSON files are not equal.
     *
     * @param  string $expectedFile
     * @param  string $actualFile
     * @param  string $message
     */
    protected function assertJsonFileNotEqualsJsonFile($expectedFile, $actualFile, $message = '')
    {
        \PHPUnit_Framework_Assert::assertJsonFileNotEqualsJsonFile($expectedFile, $actualFile, $message);
    }

    /**
     * Asserts that two JSON files are equal.
     *
     * @param  string $expectedFile
     * @param  string $actualFile
     * @param  string $message
     */
    protected function assertJsonFileEqualsJsonFile($expectedFile, $actualFile, $message = '')
    {
        \PHPUnit_Framework_Assert::assertJsonFileEqualsJsonFile($expectedFile, $actualFile, $message);
    }

    /**
     * Fails the test with message.
     *
     * @param $message
     */
    protected function fail($message)
    {
        \PHPUnit_Framework_Assert::fail($message);
    }

    /**
     * deprecated because of naming mismatch with \PHPUnit_Framework_Assert::assertGreaterThan
     * @deprecated since 1.8.1; to be removed in 2.0
     */
    protected function assertGreaterThen($expected, $actual, $message = '') {
        return $this->assertGreaterThan($expected, $actual, $message);
    }

    /**
     * deprecated because of naming mismatch with \PHPUnit_Framework_Assert::assertGreaterThanOrEqual
     * @deprecated since 1.8.1; to be removed in 2.0
     */
    protected function assertGreaterThenOrEqual($expected, $actual, $message = '') {
        return $this->assertGreaterThanOrEqual($expected, $actual, $message);
    }

    /**
     * deprecated because of naming mismatch with \PHPUnit_Framework_Assert::assertLessThan
     * @deprecated since 1.8.1; to be removed in 2.0
     */
    protected function assertLowerThen($expected, $actual, $message = '') {
        return $this->assertLessThan($expected, $actual, $message);
    }

    /**
     * deprecated because of naming mismatch with \PHPUnit_Framework_Assert::assertLessThanOrEqual
     * @deprecated since 1.8.1; to be removed in 2.0
     */
    protected function assertLowerThenOrEqual($expected, $actual, $message = '') {
        return $this->assertLessThanOrEqual($expected, $actual, $message);
    }
}
