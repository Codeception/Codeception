<?php
namespace Codeception\Lib\Console;


class MessageTest extends \Codeception\Test\Unit
{

    // tests
    public function testCut()
    {
        $message = new Message('very long text');
        $this->assertEquals('very long ', $message->cut(10)->getMessage());

        $message = new Message('очень длинный текст');
        $this->assertEquals('очень длин', $message->cut(10)->getMessage());
    }
    
    //test message cutting
    // @codingStandardsIgnoreStart
    public function testVeryLongTestNameVeryLongTestNameVeryLongTestNameVeryLongTestNameVeryLongTestNameVeryLongTestNameVeryLongTestName()
    {
        // @codingStandardsIgnoreEnd
    }

    // test multibyte message width
    public function testWidth()
    {
        $message = new Message('message example');
        $this->assertEquals('message example               ', $message->width(30)->getMessage());

        $message = new Message('пример текста');
        $this->assertEquals('пример текста                 ', $message->width(30)->getMessage());
    }
}
