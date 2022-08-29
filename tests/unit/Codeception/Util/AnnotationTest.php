<?php

declare(strict_types=1);

use Codeception\Util\Annotation;

/**
 * Class AnnotationTest
 *
 * @author davert
 * @tag codeception
 * @tag tdd
 */
class AnnotationTest extends \PHPUnit\Framework\TestCase
{
    public function testClassAnnotation()
    {
        $this->assertSame('davert', Annotation::forClass(__CLASS__)->fetch('author'));
        $this->assertSame('codeception', Annotation::forClass(__CLASS__)->fetch('tag'));
    }

    /**
     * @param $var1
     * @param $var2
     * @return null
     */
    public function testMethodAnnotation()
    {
        $this->assertSame('null', Annotation::forClass(__CLASS__)
                ->method('testMethodAnnotation')
                ->fetch('return'));
    }

    public function testMultipleClassAnnotations()
    {
        $this->assertSame(['codeception', 'tdd'], Annotation::forClass(__CLASS__)->fetchAll('tag'));
    }

    public function testMultipleMethodAnnotations()
    {
        $this->assertSame(
            ['$var1', '$var2'],
            Annotation::forClass(__CLASS__)->method('testMethodAnnotation')->fetchAll('param')
        );
    }

    public function testGetAnnotationsFromDocBlock()
    {
        $docblock = <<<EOF
@user davert
@param key1
@param key2
EOF;

        $this->assertSame(['davert'], Annotation::fetchAnnotationsFromDocblock('user', $docblock));
        $this->assertSame(['key1', 'key2'], Annotation::fetchAnnotationsFromDocblock('param', $docblock));
    }


    public function testGetAllAnnotationsFromDocBlock()
    {
        $docblock = <<<EOF
@user davert
@param key1
@param key2
EOF;

        $all = Annotation::fetchAllAnnotationsFromDocblock($docblock);
        codecept_debug($all);
        $this->assertSame([
            'user' => ['davert'],
            'param' => ['key1', 'key2']
        ], Annotation::fetchAllAnnotationsFromDocblock($docblock));
    }

    public function testValueToSupportJson()
    {
        $values = Annotation::arrayValue('{ "code": "200", "user": "davert", "email": "davert@gmail.com" }');
        $this->assertSame(['code' => '200', 'user' => 'davert', 'email' => 'davert@gmail.com'], $values);
    }

    public function testValueToSupportAnnotationStyle()
    {
        $values = Annotation::arrayValue('( code="200", user="davert", email = "davert@gmail.com")');
        $this->assertSame(['code' => '200', 'user' => 'davert', 'email' => 'davert@gmail.com'], $values);
    }

    /** @value foobar */
    public function testSingleLineAnnotation()
    {
        $this->assertSame('foobar', Annotation::forClass(__CLASS__)
                ->method('testSingleLineAnnotation')
                ->fetch('value'));
    }
}
