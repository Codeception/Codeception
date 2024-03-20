<?php

declare(strict_types=1);

namespace Codeception;

use Symfony\Component\Console\Command\Command;

class ApplicationTest extends \Codeception\PHPUnit\TestCase
{
    public function testRegisterCustomCommand()
    {
        \Codeception\Configuration::append(['extensions' => [
            'commands' => [
                \Project\Command\MyCustomCommand::class]]]);

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
