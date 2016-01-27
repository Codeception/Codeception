<?php

namespace Codception;

use Codeception\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        require_once \Codeception\Configuration::dataDir() . 'claypit/MyCustomCommand.php';
    }

    public function testRegisterCustomCommand()
    {
        \Codeception\Configuration::append(array('extensions' => array(
            'commands' => array(
                'Codeception\Command\MyCustomCommand'))));

        $application = new Application();
        $application->registerCustomCommands();

        try {
            $application->find('myProjekt:myCommand');
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
