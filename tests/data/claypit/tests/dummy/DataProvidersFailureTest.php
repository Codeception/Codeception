<?php

class DataProvidersFailureTest extends \Codeception\Test\Unit
{

    /**
     * @dataProvider rectangle
     */
    public function testIsTriangle(DumbGuy $I)
    {
        $I->amGoingTo("Fail before I get here.");
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