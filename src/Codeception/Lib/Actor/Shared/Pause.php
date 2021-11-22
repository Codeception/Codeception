<?php

declare(strict_types=1);

namespace Codeception\Lib\Actor\Shared;

use Codeception\Lib\Console\ReplHistory;
use Codeception\Util\Debug;
use Exception;
use Hoa\Console\Console as HoaConsole;
use Hoa\Console\Cursor;
use Hoa\Console\Readline\Autocompleter\Word;
use Hoa\Console\Readline\Readline;
use PHPUnit\Framework\AssertionFailedError;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

trait Pause
{
    public function pause(): void
    {
        if (!Debug::isEnabled()) {
            return;
        }

        if (!class_exists('Hoa\Console\Readline\Readline')) {
            throw new Exception('Hoa Console is not installed. Please add `hoa/console` to composer.json');
        }

        $autoStash = false;

        $I = $this;
        $output = new ConsoleOutput();
        $readline = new Readline();

        $readline->setAutocompleter(
            new Word(get_class_methods($this))
        );

        $stashFn = function (Readline $self, $isManual = true) {
            $lastCommand = $self->previousHistory();

            Cursor::clear('↔');

            if (strlen($lastCommand) > 0) {
                ReplHistory::getInstance()->add("\$I->{$lastCommand};");
                codecept_debug("Command stashed: \$I->{$lastCommand};");
            } else {
                codecept_debug("Nothing to stash.");
            }

            if ($isManual) {
                HoaConsole::getOutput()->writeAll($self->getPrefix() . $self->getLine());
            }

            $self->nextHistory();

            return Readline::STATE_CONTINUE;
        };

        $clearStashFn = function (Readline $self) {
            ReplHistory::getInstance()->clear();

            Cursor::clear('↔');

            codecept_debug("Stash cleared.");

            HoaConsole::getOutput()->writeAll($self->getPrefix() . $self->getLine());

            return Readline::STATE_CONTINUE;
        };

        $viewStashedFn = function (Readline $self) use ($output) {
            Cursor::clear('↔');
            $stashedCommands = ReplHistory::getInstance()->getAll();

            if (!empty($stashedCommands)) {
                $output->writeln("\n<comment>Stashed commands:</comment>");
                codecept_debug(implode("\n", $stashedCommands) . "\n");
            } else {
                codecept_debug("No commands stashed.");
            }

            HoaConsole::getOutput()->writeAll($self->getPrefix() . $self->getLine());

            return Readline::STATE_CONTINUE;
        };

        $toggleAutoStashFn = function (Readline $self) use (&$autoStash) {
            Cursor::clear('↔');

            $autoStash = !$autoStash;

            codecept_debug("Autostash " . ($autoStash ? 'enabled' : 'disabled') . '.');

            HoaConsole::getOutput()->writeAll($self->getPrefix() . $self->getLine());

            return Readline::STATE_CONTINUE;
        };

        $tput = HoaConsole::getTput();
        $readline->addMapping($tput->get('key_f5'), $stashFn);
        $readline->addMapping($tput->get('key_f6'), $toggleAutoStashFn);
        $readline->addMapping($tput->get('key_f8'), $viewStashedFn);
        $readline->addMapping($tput->get('key_f10'), $clearStashFn);

        $output->writeln("  <comment>Execution PAUSED, starting interactive shell...</comment>");
        $output->writeln("  Type in commands to try them:");
        $output->writeln("  - <info>ENTER</info> to continue");
        $output->writeln("  - <info>TAB</info> to auto-complete");
        $output->writeln("  - <info>F5</info> to stash a command");
        $output->writeln("  - <info>F6</info> to toggle auto-stashing of successful commands");
        $output->writeln("  - <info>F8</info> to view stashed commands");
        $output->writeln("  - <info>F10</info> to clear stashed commands");

        $result = '';

        do {
            $command = $readline->readLine('$I->'); // “> ” is the prefix of the line.

            if ($command == 'exit' || $command == '') {
                ReplHistory::getInstance()->save();
                return;
            }
            try {
                $value = eval("return \$I->{$command};");
                if ($value) {
                    $result = $value;
                    if (!is_object($result)) {
                        codecept_debug($result);
                    }
                    codecept_debug('>> Result saved to $result variable, you can use it in next commands');
                }

                if ($autoStash) {
                    call_user_func($stashFn, $readline, false);
                }
            } catch (AssertionFailedError $fail) {
                $output->writeln("<error>fail</error> " . $fail->getMessage());
            } catch (Exception $e) {
                $output->writeln("<error>error</error> " . $e->getMessage());
            } catch (Throwable $e) {
                $output->writeln("<error>syntax error</error> " . $e->getMessage());
            }
        } while (true);
    }
}
