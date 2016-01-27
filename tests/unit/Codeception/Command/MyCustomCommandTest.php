<?php
namespace Codception\Command;

class MyCustomCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testHasCodeceptionCustomCommandInterface()
    {
        $command = new \Codeception\Command\MyCustomCommand('commandName');
        $this->assertInstanceOf('Codeception\Lib\Interfaces\CustomCommands', $command);
    }

    public function testHasCommandName()
    {
        $commandName = \Codeception\Command\MyCustomCommand::getCommandName();
        $this->assertEquals('myProjekt:myCommand', $commandName);
    }
}
