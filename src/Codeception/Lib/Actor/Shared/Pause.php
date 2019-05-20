<?php
namespace Codeception\Lib\Actor\Shared;

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

        $I = $this;
        $readline = new \Hoa\Console\Readline\Readline();

        $readline->setAutocompleter(
            new \Hoa\Console\Readline\Autocompleter\Word(get_class_methods($this))
        );
        $output = new ConsoleOutput();
        $output->writeln("  <comment>Execution PAUSED, starting interactive shell...</comment>");
        $output->writeln("  Type in commands to try them, ENTER to continue, TAB to auto-complete");

        $result = '';

        do {
            $command = $readline->readLine('$I->'); // “> ” is the prefix of the line.

            if ($command == 'exit') {
                return;
            }
            if ($command == '') {
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
