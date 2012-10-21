<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Tests\Input;

use Symfony\Component\Console\Input\InputArgument;

class InputArgumentTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $argument = new InputArgument('foo');
        $this->assertEquals('foo', $argument->getName(), '__construct() takes a name as its first argument');

        // mode argument
        $argument = new InputArgument('foo');
        $this->assertFalse($argument->isRequired(), '__construct() gives a "InputArgument::OPTIONAL" mode by default');

        $argument = new InputArgument('foo', null);
        $this->assertFalse($argument->isRequired(), '__construct() can take "InputArgument::OPTIONAL" as its mode');

        $argument = new InputArgument('foo', InputArgument::OPTIONAL);
        $this->assertFalse($argument->isRequired(), '__construct() can take "InputArgument::OPTIONAL" as its mode');

        $argument = new InputArgument('foo', InputArgument::REQUIRED);
        $this->assertTrue($argument->isRequired(), '__construct() can take "InputArgument::REQUIRED" as its mode');

        try {
            $argument = new InputArgument('foo', 'ANOTHER_ONE');
            $this->fail('__construct() throws an Exception if the mode is not valid');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\Exception', $e, '__construct() throws an Exception if the mode is not valid');
            $this->assertEquals('Argument mode "ANOTHER_ONE" is not valid.', $e->getMessage());
        }
        try {
            $argument = new InputArgument('foo', -1);
            $this->fail('__construct() throws an Exception if the mode is not valid');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\Exception', $e, '__construct() throws an Exception if the mode is not valid');
            $this->assertEquals('Argument mode "-1" is not valid.', $e->getMessage());
        }
    }

    public function testIsArray()
    {
        $argument = new InputArgument('foo', InputArgument::IS_ARRAY);
        $this->assertTrue($argument->isArray(), '->isArray() returns true if the argument can be an array');
        $argument = new InputArgument('foo', InputArgument::OPTIONAL | InputArgument::IS_ARRAY);
        $this->assertTrue($argument->isArray(), '->isArray() returns true if the argument can be an array');
        $argument = new InputArgument('foo', InputArgument::OPTIONAL);
        $this->assertFalse($argument->isArray(), '->isArray() returns false if the argument can not be an array');
    }

    public function testGetDescription()
    {
        $argument = new InputArgument('foo', null, 'Some description');
        $this->assertEquals('Some description', $argument->getDescription(), '->getDescription() return the message description');
    }

    public function testGetDefault()
    {
        $argument = new InputArgument('foo', InputArgument::OPTIONAL, '', 'default');
        $this->assertEquals('default', $argument->getDefault(), '->getDefault() return the default value');
    }

    public function testSetDefault()
    {
        $argument = new InputArgument('foo', InputArgument::OPTIONAL, '', 'default');
        $argument->setDefault(null);
        $this->assertNull($argument->getDefault(), '->setDefault() can reset the default value by passing null');
        $argument->setDefault('another');
        $this->assertEquals('another', $argument->getDefault(), '->setDefault() changes the default value');

        $argument = new InputArgument('foo', InputArgument::OPTIONAL | InputArgument::IS_ARRAY);
        $argument->setDefault(array(1, 2));
        $this->assertEquals(array(1, 2), $argument->getDefault(), '->setDefault() changes the default value');

        try {
            $argument = new InputArgument('foo', InputArgument::REQUIRED);
            $argument->setDefault('default');
            $this->fail('->setDefault() throws an Exception if you give a default value for a required argument');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\Exception', $e, '->parse() throws an \InvalidArgumentException exception if an invalid option is passed');
            $this->assertEquals('Cannot set a default value except for Parameter::OPTIONAL mode.', $e->getMessage());
        }

        try {
            $argument = new InputArgument('foo', InputArgument::IS_ARRAY);
            $argument->setDefault('default');
            $this->fail('->setDefault() throws an Exception if you give a default value which is not an array for a IS_ARRAY option');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\Exception', $e, '->setDefault() throws an Exception if you give a default value which is not an array for a IS_ARRAY option');
            $this->assertEquals('A default value for an array argument must be an array.', $e->getMessage());
        }
    }
}
