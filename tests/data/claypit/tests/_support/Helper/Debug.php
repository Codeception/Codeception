<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Debug extends \Codeception\Module
{
    public function doAnAwesomeActionWithDebugFlag()
    {
        $this->assertTrue($this->isDebugEnabled());
    }

    public function doAnAwesomeActionWithoutDebugFlag()
    {
        $this->assertFalse($this->isDebugEnabled());
    }

}
