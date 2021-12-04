<?php

class DataProvidersTest extends \Codeception\Test\Unit
{
   /**
    * @var \CodeGuy
    */
    protected CodeGuy $codeGuy;

    /**
     * @group data-providers
     * @dataProvider triangles
     */
    public function testIsTriangle(int $a, int $b, int $c)
    {
        $this->assertTrue($a + $b > $c && $c+$b > $a && $a + $c > $b);
    }

    public function triangles(): array
    {
        return array(
            'real triangle' => array(3,4,5),
            array(10,12,5),
            array(7,10,15)
        );
    }

}
