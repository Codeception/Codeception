<?php

declare(strict_types=1);

namespace Codeception\Lib\Console;

class MessageTest extends \Codeception\Test\Unit
{
    protected \CodeGuy $tester;

    public function testCut()
    {
        $message = new Message('very long text');
        $this->assertSame('very long ', $message->cut(10)->getMessage());

        $message = new Message('очень длинный текст');
        $this->assertSame('очень длин', $message->cut(10)->getMessage());
    }

    //test message cutting
    public function testVeryLongTestNameVeryLongTestNameVeryLongTestNameVeryLongTestNameVeryLongTestNameVeryLongTestNameVeryLongTestName()
    {
        $this->expectNotToPerformAssertions();
    }

    // test multibyte message width
    public function testWidth()
    {
        $message = new Message('message example');
        $this->assertSame('message example               ', $message->width(30)->getMessage());

        $message = new Message('пример текста');
        $this->assertSame('пример текста                 ', $message->width(30)->getMessage());
    }
}
