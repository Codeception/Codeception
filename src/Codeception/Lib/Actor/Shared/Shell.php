<?php
namespace Codeception\Lib\Actor\Shared;

use Symfony\Component\Console\Output\ConsoleOutput;

trait Shell
{
    public function pause()
    {
        if (!\Codeception\Util\Debug::isEnabled()) {
            return;
        }

        $I = $this;
        $readline = new \Hoa\Console\Readline\Readline();

        $readline->setAutocompleter(
            new \Hoa\Console\Readline\Autocompleter\Word(get_class_methods($this))
        );
        $output = new ConsoleOutput();
        $output->writeln("  <comment>Execution PAUSED, starting interactive shell...</comment>");
        $output->writeln("  Type in commands to try them in action, 'ENTER' to continue execution");

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
                if ($value && !is_object($value)) {
                    codecept_debug($value);
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
