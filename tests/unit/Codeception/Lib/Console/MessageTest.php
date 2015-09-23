<?php
namespace Codeception\Lib\Console;


class MessageTest extends \Codeception\TestCase\Test
{

    // tests
    public function testCut()
    {
        $message = new Message('very long text');
        $this->assertEquals('very long ', $message->cut(10)->getMessage());
    }
    
    //test message cutting
    public function testVeryLongTestNameVeryLongTestNameVeryLongTestNameVeryLongTestNameVeryLongTestNameVeryLongTestNameVeryLongTestName() {}
}
 
