<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DomCrawler\Tests\Field;

use Symfony\Component\DomCrawler\Field\FileFormField;

class FileFormFieldTest extends FormFieldTestCase
{
    public function testInitialize()
    {
        $node = $this->createNode('input', '', array('type' => 'file'));
        $field = new FileFormField($node);

        $this->assertEquals(array('name' => '', 'type' => '', 'tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE, 'size' => 0), $field->getValue(), '->initialize() sets the value of the field to no file uploaded');

        $node = $this->createNode('textarea', '');
        try {
            $field = new FileFormField($node);
            $this->fail('->initialize() throws a \LogicException if the node is not an input field');
        } catch (\LogicException $e) {
            $this->assertTrue(true, '->initialize() throws a \LogicException if the node is not an input field');
        }

        $node = $this->createNode('input', '', array('type' => 'text'));
        try {
            $field = new FileFormField($node);
            $this->fail('->initialize() throws a \LogicException if the node is not a file input field');
        } catch (\LogicException $e) {
            $this->assertTrue(true, '->initialize() throws a \LogicException if the node is not a file input field');
        }
    }

    /**
     * @dataProvider getSetValueMethods
     */
    public function testSetValue($method)
    {
        $node = $this->createNode('input', '', array('type' => 'file'));
        $field = new FileFormField($node);

        $field->$method(null);
        $this->assertEquals(array('name' => '', 'type' => '', 'tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE, 'size' => 0), $field->getValue(), "->$method() clears the uploaded file if the value is null");

        $field->$method(__FILE__);
        $value = $field->getValue();

        $this->assertEquals(basename(__FILE__), $value['name'], "->$method() sets the name of the file field");
        $this->assertEquals('', $value['type'], "->$method() sets the type of the file field");
        $this->assertInternalType('string', $value['tmp_name'], "->$method() sets the tmp_name of the file field");
        $this->assertEquals(0, $value['error'], "->$method() sets the error of the file field");
        $this->assertEquals(filesize(__FILE__), $value['size'], "->$method() sets the size of the file field");
    }

    public function getSetValueMethods()
    {
        return array(
            array('setValue'),
            array('upload'),
        );
    }

    public function testSetErrorCode()
    {
        $node = $this->createNode('input', '', array('type' => 'file'));
        $field = new FileFormField($node);

        $field->setErrorCode(UPLOAD_ERR_FORM_SIZE);
        $value = $field->getValue();
        $this->assertEquals(UPLOAD_ERR_FORM_SIZE, $value['error'], '->setErrorCode() sets the file input field error code');

        try {
            $field->setErrorCode('foobar');
            $this->fail('->setErrorCode() throws a \InvalidArgumentException if the error code is not valid');
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true, '->setErrorCode() throws a \InvalidArgumentException if the error code is not valid');
        }
    }
}
