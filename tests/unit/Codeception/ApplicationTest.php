<?php

namespace Codception;

use Codeception\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testRegisterCustomCommand()
    {
        \Codeception\Configuration::append(array('extensions' => array(
            'commands' => array(
                'Codeception\Command\MyCustomCommand'))));

        $application = new Application();
        $application->registerCustomCommands();

        $command = null;

        try {
            $command = $application->find('myProjekt:myCommand');
        } catch (\Exception $e) {
        }

        $this->assertNotEmpty($command, "Custom command not found.");
    }
}
