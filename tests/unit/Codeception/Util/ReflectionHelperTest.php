<?php
namespace Codeception\Util;

use ReflectionException;
use ReflectionParameter;

require_once 'ReflectionTestClass.php';

class ReflectionHelperTest extends \Codeception\PHPUnit\TestCase
{
    public function testReadPrivateProperty()
    {
        $expected = 'fooBar123';

        $object = new ReflectionTestClass();
        $object->setValue($expected);

        $this->assertEquals(
            $expected,
            ReflectionHelper::readPrivateProperty($object, 'value', ReflectionTestClass::class)
        );

        $this->assertEquals(
            $expected,
            ReflectionHelper::readPrivateProperty($object, 'value', null)
        );

        $this->expectException(ReflectionException::class);

        $this->assertEquals(
            $expected,
            ReflectionHelper::readPrivateProperty($object, 'value', '')
        );
    }

    public function testInvokePrivateMethod()
    {
        $expected = "I'm a cat!";

        $object = new ReflectionTestClass();
        $object->setValue($expected);

        $this->assertEquals(
            $expected,
            ReflectionHelper::invokePrivateMethod($object, 'getSecret', ['cat'], ReflectionTestClass::class)
        );

        $this->assertEquals(
            $expected,
            ReflectionHelper::invokePrivateMethod($object, 'getSecret', ['cat'], null)
        );

        $this->expectException(ReflectionException::class);

        $this->assertEquals(
            $expected,
            ReflectionHelper::invokePrivateMethod($object, 'getSecret', ['cat'], '')
        );
    }

    public function testGetClassShortName()
    {
        $this->assertEquals(
            'ReflectionTestClass',
            ReflectionHelper::getClassShortName(new ReflectionTestClass())
        );

        $this->assertEquals(
            'ReflectionHelper',
            ReflectionHelper::getClassShortName(new ReflectionHelper())
        );
    }

    public function testGetClassFromParameter()
    {
        $object = new ReflectionTestClass();
        $object->setValue('elephant');

        $this->assertEquals(
            'Codeception\Util\Debug',
            ReflectionHelper::getClassFromParameter(new ReflectionParameter(array($object, 'setDebug'), 0))
        );

        $this->assertEquals(
            null,
            ReflectionHelper::getClassFromParameter(new ReflectionParameter(array($object, 'setDebug'), 1))
        );

        $this->assertEquals(
            null,
            ReflectionHelper::getClassFromParameter(new ReflectionParameter(array($object, 'setDebug'), 'flavor'))
        );

        $this->assertEquals(
            null,
            ReflectionHelper::getClassFromParameter(new ReflectionParameter(array($object, 'setInt'), 'i'))
        );
    }

    public function testGetDefaultValue()
    {
        $object = new ReflectionTestClass();
        $object->setValue('elephant');

        $this->assertEquals(
            'null',
            ReflectionHelper::getDefaultValue(new ReflectionParameter(array($object, 'setDebug'), 0))
        );

        $this->assertEquals(
            '\Codeception\Util\ReflectionTestClass::FOO',
            ReflectionHelper::getDefaultValue(new ReflectionParameter(array($object, 'setDebug'), 1))
        );

        $this->assertEquals(
            '\Codeception\Util\ReflectionTestClass::FOO',
            ReflectionHelper::getDefaultValue(new ReflectionParameter(array($object, 'setDebug'), 'flavor'))
        );

        $this->assertEquals(
            '\Codeception\Codecept::VERSION',
            ReflectionHelper::getDefaultValue(new ReflectionParameter(array($object, 'setFlavorImportedDefault'), 'flavor'))
        );

        $this->assertEquals(
            PHP_VERSION_ID < 70100 ? 'null' : "''",
            ReflectionHelper::getDefaultValue(new ReflectionParameter(array($object, 'setValue'), 0))
        );

        $this->assertEquals(
            PHP_VERSION_ID < 70100 ? 'null' : '0',
            ReflectionHelper::getDefaultValue(new ReflectionParameter(array($object, 'setInt'), 0))
        );

        $this->assertEquals(
            'null',
            ReflectionHelper::getDefaultValue(new ReflectionParameter(array($object, 'setMixed'), 0))
        );
    }

    public function testPhpEncodeValue()
    {
        $this->assertEquals(
            '0',
            ReflectionHelper::phpEncodeValue(0)
        );

        $this->assertEquals(
            '1',
            ReflectionHelper::phpEncodeValue(1)
        );

        $this->assertEquals(
            '"\\u00fcber"',
            ReflectionHelper::phpEncodeValue('über')
        );

        $this->assertEquals(
            '["foo" => "bar"]',
            ReflectionHelper::phpEncodeValue(['foo' => 'bar'])
        );

        $this->assertEquals(
            '["foo" => "bar", "baz" => ["cat", "dog"]]',
            ReflectionHelper::phpEncodeValue(['foo' => 'bar', 'baz' => ['cat', 'dog']])
        );
    }

    public function testPhpEncodeArray()
    {
        $this->assertEquals(
            '["foo" => "bar"]',
            ReflectionHelper::phpEncodeArray(['foo' => 'bar'])
        );

        $this->assertEquals(
            '["foo" => "bar", "baz" => ["cat", "dog"]]',
            ReflectionHelper::phpEncodeArray(['foo' => 'bar', 'baz' => ['cat', 'dog']])
        );
    }
}