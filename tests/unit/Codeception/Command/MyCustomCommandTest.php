<?php
namespace Project\Command;

class MyCustomCommandTest extends \Codeception\PHPUnit\TestCase
{
    public static function _setUpBeforeClass()
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
