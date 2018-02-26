<?php
namespace Project\Command;

class MyCustomCommandTest extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass()
    {
        require_once \Codeception\Configuration::dataDir() . 'register_command/examples/MyCustomCommand.php';
    }

    public function testHasCodeceptionCustomCommandInterface()
    {
        $command = new MyCustomCommand('commandName');
        $this->assertInstanceOf('Codeception\CustomCommandInterface', $command);
    }

    public function testHasCommandName()
    {
        $commandName = MyCustomCommand::getCommandName();
        $this->assertEquals('myProject:myCommand', $commandName);
    }
}
