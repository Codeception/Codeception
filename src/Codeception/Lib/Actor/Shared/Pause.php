<?php
namespace Codeception\Lib\Actor\Shared;

use Codeception\Lib\Console\ReplHistory;
use Symfony\Component\Console\Output\ConsoleOutput;

trait Pause
{
    public function pause()
    {
        if (!\Codeception\Util\Debug::isEnabled()) {
            return;
        }

        if (!class_exists('Hoa\Console\Readline\Readline')) {
            throw new \Exception('Hoa Console is not installed. Please add `hoa/console` to composer.json');
        }

        $autoStash = false;

        $I = $this;
        $output = new ConsoleOutput();
        $readline = new \Hoa\Console\Readline\Readline();

        $readline->setAutocompleter(
            new \Hoa\Console\Readline\Autocompleter\Word(get_class_methods($this))
        );

        $stashFn = function (\Hoa\Console\Readline\Readline $self, $isManual = true) {
            $lastCommand = $self->previousHistory();

            \Hoa\Console\Cursor::clear('↔');

            if (strlen($lastCommand) > 0) {
                ReplHistory::getInstance()->add("\$I->{$lastCommand};");
                codecept_debug("Command stashed: \$I->{$lastCommand};");
            } else {
                codecept_debug("Nothing to stash.");
            }

            if ($isManual) {
                \Hoa\Console\Console::getOutput()->writeAll($self->getPrefix() . $self->getLine());
            }

            $self->nextHistory();

            return \Hoa\Console\Readline\Readline::STATE_CONTINUE;
        };

        $clearStashFn = function (\Hoa\Console\Readline\Readline $self) {
            ReplHistory::getInstance()->clear();

            \Hoa\Console\Cursor::clear('↔');

            codecept_debug("Stash cleared.");

            \Hoa\Console\Console::getOutput()->writeAll($self->getPrefix() . $self->getLine());

            return \Hoa\Console\Readline\Readline::STATE_CONTINUE;
        };

        $viewStashedFn = function (\Hoa\Console\Readline\Readline $self) use ($output) {
            \Hoa\Console\Cursor::clear('↔');
            $stashedCommands = ReplHistory::getInstance()->getAll();

            if (!empty($stashedCommands)) {
                $output->writeln("\n<comment>Stashed commands:</comment>");
                codecept_debug(implode("\n", $stashedCommands) . "\n");
            } else {
                codecept_debug("No commands stashed.");
            }

            \Hoa\Console\Console::getOutput()->writeAll($self->getPrefix() . $self->getLine());

            return \Hoa\Console\Readline\Readline::STATE_CONTINUE;
        };

        $toggleAutoStashFn = function (\Hoa\Console\Readline\Readline $self) use (&$autoStash) {
            \Hoa\Console\Cursor::clear('↔');

            $autoStash = !$autoStash;

            codecept_debug("Autostash " . ($autoStash ? 'enabled' : 'disabled') . '.');

            \Hoa\Console\Console::getOutput()->writeAll($self->getPrefix() . $self->getLine());

            return \Hoa\Console\Readline\Readline::STATE_CONTINUE;
        };

        $tput = \Hoa\Console\Console::getTput();
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
                $value = eval("return \$I->$command;");
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
            } catch (\PHPUnit\Framework\AssertionFailedError $fail) {
                $output->writeln("<error>fail</error> " . $fail->getMessage());
            } catch (\Exception $e) {
                $output->writeln("<error>error</error> " . $e->getMessage());
            } catch (\Throwable $e) {
                $output->writeln("<error>syntax error</error> " . $e->getMessage());
            }
        } while (true);
    }
}
