<?php
namespace Codception\Command;

class MyCustomCommandTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        require_once \Codeception\Configuration::dataDir() . 'claypit/MyCustomCommand.php';
    }

    public function testHasCodeceptionCustomCommandInterface()
    {
        $command = new \Codeception\Command\MyCustomCommand('commandName');
        $this->assertInstanceOf('Codeception\Lib\Interfaces\CustomCommand', $command);
    }

    public function testHasCommandName()
    {
        $commandName = \Codeception\Command\MyCustomCommand::getCommandName();
        $this->assertEquals('myProjekt:myCommand', $commandName);
    }
}
