<?php

declare(strict_types=1);

namespace Codeception;

class ApplicationTest extends \Codeception\PHPUnit\TestCase
{

    public static function _setUpBeforeClass()
    {
        require_once \Codeception\Configuration::dataDir() . 'register_command/examples/MyCustomCommand.php';
    }

    public function testRegisterCustomCommand()
    {
        \Codeception\Configuration::append(['extensions' => [
            'commands' => [
                'Project\Command\MyCustomCommand']]]);

        $application = new Application();
        $application->registerCustomCommands();

        try {
            $application->find('myProject:myCommand');
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
