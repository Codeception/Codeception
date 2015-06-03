<?php

use \Codeception\Util\Multibyte;

class MultibyteTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider dataStrlen
     */
    public function testStrlen($string, $expected)
    {
        $this->assertSame($expected, Multibyte::strlen($string));
    }

    public function dataStrlen()
    {
        return array(
            array('STRING', 6),
            array('awesome PHP', 11),
            array('Τάχιστη αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός', 61),
            array('本日梅雨入りしました', 10),
        );
    }

    /**
     * @dataProvider dataStrwidth
     */
    public function testStrwidth($string, $expected)
    {
        $this->assertSame($expected, Multibyte::strwidth($string));
    }

    public function dataStrwidth()
    {
        return array(
            array('STRING', 6),
            array('awesome PHP', 11),
            array('Τάχιστη αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός', 61),
            array('本日梅雨入りしました', 20),
        );
    }

    /**
     * @dataProvider dataStrtolower
     */
    public function testStrtolower($string, $expected)
    {
        $this->assertSame($expected, Multibyte::strtolower($string));
    }

    public function dataStrtolower()
    {
        return array(
            array('STRING', 'string'),
            array('awesome PHP', 'awesome php'),
            array('Τάχιστη αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός', 'τάχιστη αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός'),
        );
    }

    /**
     * @dataProvider dataStrtoupper
     */
    public function testStrtoupper($string, $expected)
    {
        $this->assertSame($expected, Multibyte::strtoupper($string));
    }

    public function dataStrtoupper()
    {
        return array(
            array('string', 'STRING'),
            array('awesome PHP', 'AWESOME PHP'),
            array('Τάχιστη αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός', 'ΤΆΧΙΣΤΗ ΑΛΏΠΗΞ ΒΑΦΉΣ ΨΗΜΈΝΗ ΓΗ, ΔΡΑΣΚΕΛΊΖΕΙ ΥΠΈΡ ΝΩΘΡΟΎ ΚΥΝΌΣ'),
        );
    }

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
