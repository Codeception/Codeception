<?php

use Codeception\Attribute\DataProvider;
use Codeception\Attribute\Group;
use Codeception\Test\Unit;

final class DataProvidersTest extends Unit
{
    protected CodeGuy $codeGuy;

    #[Group('data-providers')]
    /**
     * @dataProvider triangles
     */
    public function testIsTriangle(int $a, int $b, int $c)
    {
        $this->assertTrue($a + $b > $c && $c + $b > $a && $a + $c > $b);
    }

    public function triangles(): array
    {
        return [
            'real triangle' => [3,4,5],
            [10,12,5],
            [7,10,15]
        ];
    }
}
