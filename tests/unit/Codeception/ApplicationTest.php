<?php

declare(strict_types=1);

namespace Codeception;

use Symfony\Component\Console\Command\Command;

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
            $command = $application->find('myProject:myCommand');
            $this->assertInstanceOf(Command::class, $command);
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }
}
