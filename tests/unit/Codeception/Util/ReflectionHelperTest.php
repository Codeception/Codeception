<?php

declare(strict_types=1);

namespace Codeception\Util;

use ReflectionException;
use ReflectionParameter;

class ReflectionHelperTest extends \Codeception\PHPUnit\TestCase
{
    public function testReadPrivateProperty()
    {
        $expected = 'fooBar123';

        $object = new ReflectionTestClass();
        $object->setValue($expected);

        $this->assertSame(
            $expected,
            ReflectionHelper::readPrivateProperty($object, 'value', ReflectionTestClass::class)
        );

        $this->assertSame(
            $expected,
            ReflectionHelper::readPrivateProperty($object, 'value', null)
        );

        $this->expectException(ReflectionException::class);

        $this->assertSame(
            $expected,
            ReflectionHelper::readPrivateProperty($object, 'value', '')
        );
    }

    public function testInvokePrivateMethod()
    {
        $expected = "I'm a cat!";

        $object = new ReflectionTestClass();
        $object->setValue($expected);

        $this->assertSame(
            $expected,
            ReflectionHelper::invokePrivateMethod($object, 'getSecret', ['cat'], ReflectionTestClass::class)
        );

        $this->assertSame(
            $expected,
            ReflectionHelper::invokePrivateMethod($object, 'getSecret', ['cat'], null)
        );

        $this->expectException(ReflectionException::class);

        $this->assertSame(
            $expected,
            ReflectionHelper::invokePrivateMethod($object, 'getSecret', ['cat'], '')
        );
    }

    public function testGetClassShortName()
    {
        $this->assertSame(
            'ReflectionTestClass',
            ReflectionHelper::getClassShortName(new ReflectionTestClass())
        );

        $this->assertSame(
            'ReflectionHelper',
            ReflectionHelper::getClassShortName(new ReflectionHelper())
        );
    }

    public function testGetClassFromParameter()
    {
        $object = new ReflectionTestClass();
        $object->setValue('elephant');

        $this->assertSame(
            \Codeception\Util\Debug::class,
            ReflectionHelper::getClassFromParameter(new ReflectionParameter([$object, 'setDebug'], 0))
        );

        $this->assertNull(
            ReflectionHelper::getClassFromParameter(new ReflectionParameter([$object, 'setDebug'], 1))
        );

        $this->assertNull(
            ReflectionHelper::getClassFromParameter(new ReflectionParameter([$object, 'setDebug'], 'flavor'))
        );

        $this->assertNull(
            ReflectionHelper::getClassFromParameter(new ReflectionParameter([$object, 'setInt'], 'i'))
        );
    }

    public function testGetDefaultValue()
    {
        $object = new ReflectionTestClass();
        $object->setValue('elephant');

        $this->assertSame(
            'null',
            ReflectionHelper::getDefaultValue(new ReflectionParameter([$object, 'setDebug'], 0))
        );

        $this->assertSame(
            '\Codeception\Util\ReflectionTestClass::FOO',
            ReflectionHelper::getDefaultValue(new ReflectionParameter([$object, 'setDebug'], 1))
        );

        $this->assertSame(
            '\Codeception\Util\ReflectionTestClass::FOO',
            ReflectionHelper::getDefaultValue(new ReflectionParameter([$object, 'setDebug'], 'flavor'))
        );

        $this->assertSame(
            '\Codeception\Codecept::VERSION',
            ReflectionHelper::getDefaultValue(new ReflectionParameter([$object, 'setFlavorImportedDefault'], 'flavor'))
        );

        $this->assertSame(
            "''",
            ReflectionHelper::getDefaultValue(new ReflectionParameter([$object, 'setValue'], 0))
        );

        $this->assertSame(
            '0',
            ReflectionHelper::getDefaultValue(new ReflectionParameter([$object, 'setInt'], 0))
        );

        $this->assertSame(
            'null',
            ReflectionHelper::getDefaultValue(new ReflectionParameter([$object, 'setMixed'], 0))
        );
    }

    public function testPhpEncodeValue()
    {
        $this->assertSame(
            '0',
            ReflectionHelper::phpEncodeValue(0)
        );

        $this->assertSame(
            '1',
            ReflectionHelper::phpEncodeValue(1)
        );

        $this->assertSame(
            '"\\u00fcber"',
            ReflectionHelper::phpEncodeValue('über')
        );

        $this->assertSame(
            '["foo" => "bar"]',
            ReflectionHelper::phpEncodeValue(['foo' => 'bar'])
        );

        $this->assertSame(
            '["foo" => "bar", "baz" => ["cat", "dog"]]',
            ReflectionHelper::phpEncodeValue(['foo' => 'bar', 'baz' => ['cat', 'dog']])
        );
    }

    public function testPhpEncodeArray()
    {
        $this->assertSame(
            '["foo" => "bar"]',
            ReflectionHelper::phpEncodeArray(['foo' => 'bar'])
        );

        $this->assertSame(
            '["foo" => "bar", "baz" => ["cat", "dog"]]',
            ReflectionHelper::phpEncodeArray(['foo' => 'bar', 'baz' => ['cat', 'dog']])
        );
    }
}
