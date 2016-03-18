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

    // skip if mb_substr not exists
    public function testUTF8Message()
    {
        if (function_exists('mb_substr')) {
            $message = new Message('очень длинный текст');
            $this->assertEquals('очень длин', $message->cut(10)->getMessage());
        }
    }

    // skip if mb_strlen not exists
    public function testUTF8Width()
    {
        if (function_exists('mb_strlen')) {
            $message = new Message('пример текста');
            $this->assertEquals(
                'пример текста                 ',
                $message->width(30)->getMessage())
            ;
        }
    }
}
 
