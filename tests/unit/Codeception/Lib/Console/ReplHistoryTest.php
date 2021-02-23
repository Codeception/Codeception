<?php

declare(strict_types=1);

namespace Codeception\Lib\Console;

use Codeception\Configuration;
use Codeception\Test\Unit;

class ReplHistoryTest extends Unit
{
    /**
     * @var ReplHistory
     */
    protected $replHistory;

    protected function _setUp(): void
    {
        $this->replHistory = ReplHistory::getInstance();
    }

    protected function _tearDown(): void
    {
        $this->replHistory->clear();
    }

    // tests
    public function testAdd(): void
    {
        $this->replHistory->add('$I->click(".something")');
        $this->replHistory->add('$I->anotherCommand()');

        $commands = $this->replHistory->getAll();
        $this->assertCount(2, $commands);
        $this->assertEquals('$I->click(".something")', $commands[0]);
        $this->assertEquals('$I->anotherCommand()', $commands[1]);
    }

    public function testClear(): void
    {
        $this->replHistory->add('$I->click(".command-1")');
        $this->replHistory->add('$I->click(".command-2")');
        $this->replHistory->add('$I->click(".command-3")');

        $this->replHistory->clear();

        $this->assertCount(0, $this->replHistory->getAll());
    }

    public function testSave(): void
    {
        $this->replHistory->add('$I->click(".command-1");');
        $this->replHistory->add('$I->click(".command-2");');
        $this->replHistory->save();

        $this->replHistory->add('$I->click(".command-3");');
        $this->replHistory->save();

        $history = Configuration::outputDir() . 'stashed-commands';
        $this->assertFileExists($history);
        $this->assertStringEqualsFile($history, <<<CONTENTS
\$I->click(".command-1");
\$I->click(".command-2");
\$I->click(".command-3");

CONTENTS
        );
    }
}
