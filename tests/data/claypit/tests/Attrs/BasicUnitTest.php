<?php

namespace Attrs;

use Codeception\Attribute\Examples as ExampleAttr;
use Codeception\Attribute\Group;
use Codeception\Attribute\Incomplete;
use Codeception\Attribute\Skip;
use Codeception\Test\Unit;

class BasicUnitTest extends Unit
{
    #[Group('uincomplete')]
    #[Incomplete]
    public function testIncomplete()
    {
        $this->assertEquals(1, 2);
    }

    #[Group('uskip')]
    #[Skip]
    public function testSkip()
    {
        $this->assertEquals(1, 2);
    }

    #[Skip]
    #[Group('ue1')]
    #[ExampleAttr([1, 1], [2, 2])]
    public function testExample($e)
    {
        $this->assertEquals($e[1], $e[0]);
    }

    #[Group('ud1')]
    #[Skip]
    #[DataProvider('_listItems')]
    public function testDataProvider($e1, $e2)
    {
        $this->assertEquals($e1, $e2);
    }

    private function _listItems()
    {
        return [
            [1,1],
            [2,2],
        ];
    }
}
