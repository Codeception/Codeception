<?php
use \Codeception\Util\Annotation;
/**
 * Class AnnotationTest
 *
 * @author davert
 * @tag codeception
 * @tag tdd
 */
class AnnotationTest extends PHPUnit_Framework_TestCase {

    public function testClassAnnotation()
    {
        $this->assertEquals('davert', Annotation::forClass(__CLASS__)->fetch('author'));
        $this->assertEquals('codeception', Annotation::forClass(__CLASS__)->fetch('tag'));
    }

    /**
     * @param $var1
     * @param $var2
     * @return null
     */
    public function testMethodAnnotation()
    {
        $this->assertEquals('null', Annotation::forClass(__CLASS__)
                ->method('testMethodAnnotation')
                ->fetch('return'));
    }

    public function testMultipleClassAnnotations()
    {
        $this->assertEquals(array('codeception', 'tdd'), Annotation::forClass(__CLASS__)->fetchAll('tag'));
    }

    public function testMultipleMethodAnnotations()
    {
        $this->assertEquals(array('$var1', '$var2'),
            Annotation::forClass(__CLASS__)->method('testMethodAnnotation')->fetchAll('param')
        );
    }


}
