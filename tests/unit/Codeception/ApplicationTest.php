<?php

namespace Codeception;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        require_once \Codeception\Configuration::dataDir() . 'register_command/examples/MyCustomCommand.php';
    }

    public function testRegisterCustomCommand()
    {
        \Codeception\Configuration::append(array('extensions' => array(
            'commands' => array(
                'Project\Command\MyCustomCommand'))));

        $application = new Application();
        $application->registerCustomCommands();

        try {
            $application->find('myProject:myCommand');
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
