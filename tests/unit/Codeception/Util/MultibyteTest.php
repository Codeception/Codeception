<?php

use \Codeception\Util\Multibyte;

class MultibyteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataUcfirst
     */
    public function testUcfirst($string, $expected)
    {
        $this->assertSame($expected, Multibyte::ucfirst($string));
    }

    public function dataUcfirst()
    {
        return array(
            array('string', 'String'),
            array('awesome PHP', 'Awesome PHP'),
            array('ψημένη γη', 'Ψημένη γη'),
        );
    }
}
