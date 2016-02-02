<?php
namespace Codception\Command;

class MyCustomCommandTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        require_once \Codeception\Configuration::dataDir() . 'register_command/examples/MyCustomCommand.php';
    }

    public function testHasCodeceptionCustomCommandInterface()
    {
        $command = new \Codeception\Command\MyCustomCommand('commandName');
        $this->assertInstanceOf('Codeception\CustomCommandInterface', $command);
    }

    public function testHasCommandName()
    {
        $commandName = \Codeception\Command\MyCustomCommand::getCommandName();
        $this->assertEquals('myProject:myCommand', $commandName);
    }
}
