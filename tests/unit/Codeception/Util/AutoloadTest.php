<?php


use Codeception\Util\Autoload;

class AutoloadTest extends PHPUnit_Framework_TestCase {

    public function testMatches()
    {
        $this->assertTrue(Autoload::matches('api\frontend\UserHelper', 'api\frontend', 'Helper'));
        $this->assertTrue(Autoload::matches('\api\frontend\ModelHelper', 'api\frontend', 'Helper'));
        $this->assertTrue(Autoload::matches('\api\Codeception\UserController', 'api\Codeception', 'Controller'));
        $this->assertTrue(Autoload::matches('\api\Codeception\UserController', 'api\Codeception', 'Controller'));
        $this->assertTrue(Autoload::matches('\api\Codeception\UserController', '', 'Controller'));
    }

}
