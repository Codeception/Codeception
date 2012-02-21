<?php
namespace Codeception\Subscriber;

use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Console implements EventSubscriberInterface
{
    protected $steps = true;
    protected $debug = false;
    protected $color = true;
    protected $silent = false;

    protected $lastTestFailed = false;

    protected $traceLength = 5;

    public function __construct($options)
    {
        $this->silent = $options['silent'];
        $this->debug = !$this->silent && $options['debug'];
        $this->steps = !$this->silent && ($options['steps'] or $options['debug']);
        $this->color = $options['colors'];
        $this->output = new \Codeception\Output($this->color);
    }

    // triggered for all tests
    public function startTest(\Codeception\Event\Test $e)
    {
        if ($this->silent) return;
        if ($e->getTest() instanceof \Codeception\TestCase) return;
        $this->output->put("Running [[" . $e->getTest()->toString() . "]] ");
    }

    // triggered for scenario based tests: cept, cest
    public function beforeTest(\Codeception\Event\Test $e)
    {
        if ($this->silent) return;
        $test = $e->getTest();
        $this->output->put("Trying to [[{$test->getFeature()}]] ({$test->getFileName()})");
        if ($this->steps && count($e->getTest()->getScenario()->getSteps())) $this->output->writeln("\nScenario:");
    }

    public function afterTest(\Codeception\Event\Test $e)
    {
    }


    public function endTest(\Codeception\Event\Test $e)
    {
        $test = $e->getTest();
        if (!$this->lastTestFailed) $this->formattedTestOutput($test, 'Ok', '.');
        $this->lastTestFailed = FALSE;
    }

    public function testFail(\Codeception\Event\Fail $e)
    {
        $this->formattedTestOutput($e->getTest(), '(!Failed!)', 'F');
        $this->lastTestFailed = TRUE;
    }

    public function testError(\Codeception\Event\Fail $e)
    {
        $this->formattedTestOutput($e->getTest(), '(!Error!)', 'E');
        $this->lastTestFailed = TRUE;
    }

    public function testSkipped(\Codeception\Event\Fail $e)
    {
        $this->formattedTestOutput($e->getTest(), 'Skipped', 'S');
        $this->lastTestFailed = TRUE;
    }

    public function testIncomplete(\Codeception\Event\Fail $e)
    {
        $this->formattedTestOutput($e->getTest(), 'Incomplete', 'I');
        $this->lastTestFailed = TRUE;
    }

    protected function formattedTestOutput($test, $long)
    {
        if ($this->silent) return;

        if (!($test instanceof \Codeception\TestCase)) {
            $this->output->writeln('- ' . $long);
        } elseif (!$this->steps) {
            $this->output->writeln(" - $long");
        } else {
            $long = strtoupper($long);
            $this->output->writeln("  (%$long%)\n");
        }
    }

    public function beforeComment(\Codeception\Event\Step $e) {
        if ($this->steps) $this->output->writeln("((".$e->getStep()->__toString()."))");
    }

    public function afterComment(\Codeception\Event\Step $e) {
    }

    public function beforeStep(\Codeception\Event\Step $e)
    {
        if ($this->steps) $this->output->writeln("* " . $e->getStep()->__toString());
    }

    public function afterStep(\Codeception\Event\Step $e)
    {
        if (!$this->debug) return;
        $step = $e->getStep();
        $action = $step->getAction();
        $activeModule = \Codeception\SuiteManager::$modules[\Codeception\SuiteManager::$actions[$action]];
        if ($output = $activeModule->_getDebugOutput()) {
            $this->output->debug($output);
        }
    }

    public function beforeSuite(\Codeception\Event\Suite $e)
    {
        $this->output->writeln("");
        $this->output->writeln("Suite (({$e->getSuite()->getName()})) started");

    }

    public function afterSuite(\Codeception\Event\Suite $e)
    {
    }

    public function printFail(\Codeception\Event\Fail $e)
    {
        $failedTest = $e->getTest();
        $fail = $e->getFail();
        $failToString = \PHPUnit_Framework_TestFailure::exceptionToString($fail);

        $feature = $failedTest->getScenario()->getFeature();
        $this->output->put("\nCouldn't $feature");
        $this->output->put(" ({$failedTest->getFilename()})\n");

        $trace = array_reverse($failedTest->getTrace());
        $length = $i = count($trace);
        $last = array_shift($trace);
        if (!method_exists($last, 'getHumanizedAction')) {
            if (!$this->debug) {
                $this->output->writeln($failToString);
                return;
            }
            $this->output->writeln($this->printException('not an action', $fail));
            return;
        }
        $action = $last->getHumanizedAction();
        if (strpos($action, "am") === 0) {
            $action = 'become' . substr($action, 2);
        }

        // it's exception
        if (!($fail instanceof \PHPUnit_Framework_AssertionFailedError)) {
            if ($this->debug) {
                $this->printException($last->getAction(), $fail);
            } else {
                $this->output->writeln('to see the stack trace run this test with --debug option');
            }
            return;
        };

        // it's assertion

        if (strpos($action, "don't") === 0) {
            $action = substr($action, 6);
            $this->output->writeln("\nGuy unexpectedly managed to $action {$failToString}");
        } else {
            $this->output->writeln("Guy couldn't $action $failToString");
        }

        $this->output->writeln("  $i. (!$last!)");
        foreach ($trace as $step) {
            $i--;
            $this->output->writeln("  $i. " . $step);
            if (($length - $i - 1) >= $this->traceLength) break;
        }
        $this->output->writeln("");
    }

    public function printException($action, \Exception $e)
    {
        $i = 0;
        $class = get_class($e);
        $this->output->writeln("  Exception thrown " . $class . ":\n  (!" . $e->getMessage().'!)');
        $this->output->writeln("  Stack trace:");
        foreach ($e->getTrace() as $step) {
            $i++;
            if (strpos($step['function'], $action) !== false) break;
            $this->output->writeln(sprintf("   #%s ((%s)) %s:%s",
                $i,
                isset($step['function']) ? $step['function'] : '',
                isset($step['file']) ? $step['file'] : '',
                isset($step['line']) ? $step['line'] : ''));
            if ($i == 1) {
                if (isset($step['arguments'])) {
                    if (count($step['arguments'])) {
                        $this->output->put("        ((Arguments:))");
                        foreach ($step['args'] as $arg) {
                            $this->output->writeln("            " . json_encode($arg) . ",");
                        }
                    }
                }
            }
        }
        $this->output->writeln("\nIf it's a Codeception bug, please report it to GitHub");
    }

    static function getSubscribedEvents()
    {
        return array(
            'suite.before' => 'beforeSuite',
            'suite.after' => 'afterSuite',
            'test.before' => 'beforeTest',
            'test.after' => 'afterTest',
            'test.start' => 'startTest',
            'test.end' => 'endTest',
            'step.before' => 'beforeStep',
            'step.after' => 'afterStep',
            'comment.before' => 'beforeComment',
            'comment.after' => 'afterComment',
            'fail.fail' => 'testFail',
            'fail.error' => 'testError',
            'fail.incomplete' => 'testIncomplete',
            'fail.skipped' => 'testSkipped',
            'fail.print' => 'printFail'
        );
    }


}
