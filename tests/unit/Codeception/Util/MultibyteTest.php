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
     * @dataProvider dataSubstr
     */
    public function testSubstr($string, $start, $length, $expected)
    {
        $this->assertSame($expected, Multibyte::substr($string, $start, $length));
    }

    public function dataSubstr()
    {
        return array(
            array('abcdef', 1, null, 'bcdef'),
            array('abcdef', 2, null, 'cdef'),
            array('abcdef', -2, null, 'ef'),
            array('abcdef', -2, 1, 'e'),
            array('abcdef', 0, -1, 'abcde'),
            array('abcdef', 2, -1, 'cde'),
            array('abcdef', -3, -1, 'de'),
            array('abcdef', 0, 0, ''),
            array('abcdef', 4, -4, ''),
            array('6月25日に限定版DVDが発売されます', 6, null, '限定版DVDが発売されます'),
            array('6月25日に限定版DVDが発売されます', 6, 6, '限定版DVD'),
            array('6月25日に限定版DVDが発売されます', -10, null, 'DVDが発売されます'),
            array('6月25日に限定版DVDが発売されます', -10, 4, 'DVDが'),
        );
    }

    /**
     * @dataProvider dataStrimwidth
     */
    public function testStrimwidth($string, $start, $width, $trimmarker, $expected)
    {
        $this->assertSame($expected, Multibyte::strimwidth($string, $start, $width, $trimmarker));
    }

    public function dataStrimwidth()
    {
        return array(
            array('abcdef', 1, 3, '', 'bcd'),
            array('abcdef', 2, 10, '', 'cdef'),
            array('abcdef', 0, 5, '...', 'ab...'),
            array('abcdef', 0, 0, '', ''),
            array('abcdef', 0, 0, '...', '...'),
            array('abcdef', 0, -1, '', ''),
            array('abcdef', 0, -1, '...', ''),
            array('6月25日に限定版DVDが発売されます', 0, 7, '', '6月25日'),
            array('6月25日に限定版DVDが発売されます', 0, 8, '', '6月25日'),
            array('6月25日に限定版DVDが発売されます', 6, 12, '...', '限定版DVD...'),
            array('6月25日に限定版DVDが発売されます', 6, 12, '。。。', '限定版。。。'),
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
