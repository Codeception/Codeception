<?php

class DataProvidersTest extends \Codeception\Test\Unit
{
   /**
    * @var \CodeGuy
    */
    protected $codeGuy;

    /**
     * @group data-providers
     * @dataProvider triangles
     */
    public function testIsTriangle($a, $b, $c)
    {
        $this->assertTrue($a + $b > $c and $c+$b > $a and $a + $c > $b);
    }

    public function triangles()
    {
        return array(
            'real triangle' => array(3,4,5),
            array(10,12,5),
            array(7,10,15)
        );
    }

}