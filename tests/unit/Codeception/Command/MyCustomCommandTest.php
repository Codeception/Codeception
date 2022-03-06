<?php

declare(strict_types=1);

namespace Project\Command;

class MyCustomCommandTest extends \Codeception\PHPUnit\TestCase
{
    public function testHasCodeceptionCustomCommandInterface()
    {
        $command = new MyCustomCommand('commandName');
        $this->assertInstanceOf(\Codeception\CustomCommandInterface::class, $command);
    }

    public function testHasCommandName()
    {
        $commandName = MyCustomCommand::getCommandName();
        $this->assertSame('myProject:myCommand', $commandName);
    }
}
