<?php
namespace Codeception\Util;


class HttpCodeTest extends \Codeception\Test\Unit
{
    public function testHttpCodeConstants()
    {
        $this->assertEquals(200, HttpCode::OK);
        $this->assertEquals(404, HttpCode::NOT_FOUND);
    }

    public function testHttpCodeWithDescription()
    {
        $this->assertEquals('200 (OK)', HttpCode::getDescription(200));
        $this->assertEquals('301 (Moved Permanently)', HttpCode::getDescription(301));
        $this->assertEquals('401 (Unauthorized)', HttpCode::getDescription(401));
    }
}