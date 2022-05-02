<?php

namespace Attrs;

use \AttrsTester;
use Codeception\Example;
use Codeception\Test\Unit;

class BasicUnitTest extends Unit
{
    #[\group('uincomplete')]
    #[\incomplete]
    public function testIncomplete()
    {
        $this->assertEquals(1, 2);
    }

    #[\group('uskip')]
    #[\skip]
    public function testSkip()
    {
        $this->assertEquals(1, 2);
    }

    #[\skip]
    #[\group('ue1')]
    #[\example([1, 1], [2, 2])]
    public function testExample($e)
    {
        $this->assertEquals($e[1], $e[0]);
    }

    #[\group('ud1')]
    #[\skip]
    #[\dataProvider('_listItems')]
    public function testDataProvider($e1, $e2)
    {
        $this->assertEquals($e1, $e2);
    }

    private function _listItems() {
        return [
            [1,1],
            [2,2],
        ];
    }
}
