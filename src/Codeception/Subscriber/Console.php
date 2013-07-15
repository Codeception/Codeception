<?php
namespace Codeception\Subscriber;

use Codeception\Exception\ConditionalAssertionFailed;
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
        $this->debug = $options['debug'];
        $this->steps = $this->debug || $options['steps'];
        $this->color = $options['colors'];
        $this->output = new \Codeception\Output($this->color, $options['defer-flush']);
    }

    // triggered for all tests
    public function startTest(\Codeception\Event\Test $e)
    {
        $test = $e->getTest();
        if ($test->getFeature()) {
            $this->output->put("Trying to [[{$test->getFeature()}]] ({$test->getFileName()})");
        } else {
            $this->output->put("Running {$test->getFileName()}");
        }
        if ($this->steps && count($e->getTest()->getScenario()->getSteps())) $this->output->writeln("\nScenario:");

    }

    // triggered for scenario based tests: cept, cest
    public function before(\Codeception\Event\Test $e)
    {
    }

    public function afterTest(\Codeception\Event\Test $e)
    {
    }

    public function testSuccess(\Codeception\Event\Test $e)
    {
        $this->formattedTestOutput($e->getTest(), '(%  Passed  %)', '((Ok))');
    }

    public function endTest(\Codeception\Event\Test $e)
    {
    }

    public function testFail(\Codeception\Event\Fail $e)
    {
        if (!$this->steps && ($e->getFail() instanceof ConditionalAssertionFailed)) {
            $this->output->put(" (![F]!)");
            return;
        }
        $this->formattedTestOutput($e->getTest(), '(! Failed !)', '(!Failed!)');
    }

    public function testError(\Codeception\Event\Fail $e)
    {
        $this->formattedTestOutput($e->getTest(), '(! Error !)', '(!Error!)');
    }

    public function testSkipped(\Codeception\Event\Fail $e)
    {
        $this->formattedTestOutput($e->getTest(), 'Skipped', 'Skipped');
    }

    public function testIncomplete(\Codeception\Event\Fail $e)
    {
        $this->formattedTestOutput($e->getTest(), 'Incomplete', 'Incomplete');
    }

    protected function formattedTestOutput($test, $long, $short)
    {
        if (!($test instanceof \Codeception\TestCase\Cept)) {
            $this->output->writeln(' - ' . $short);
        } elseif (!$this->steps or (!count($test->getScenario()->getSteps()))) {
            $this->output->writeln(" - $short");
        } else {
            $long = strtoupper($long);
            $this->output->writeln("  $long\n");
        }
    }

    public function beforeStep(\Codeception\Event\Step $e)
    {
        if (!$this->steps) return;
        if ($e->getStep()->getName() == 'Comment') {
            $this->output->writeln("\n((".$e->getStep()."))");
        } else {
            $this->output->writeln("* " . $e->getStep());
        }
    }

    public function afterStep(\Codeception\Event\Step $e)
    {
        if (!$this->debug) return;
        if ($output = $e->getStep()->pullDebugOutput()) {
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
        if ($fail instanceof \PHPUnit_Framework_SelfDescribing) {
            $failToString = \PHPUnit_Framework_TestFailure::exceptionToString($fail);
        } else {
            $failToString = sprintf("[%s]\n%s", get_class($fail),$fail->getMessage());
        }

        $feature = $failedTest->getScenario()->getFeature();
        if ($e->getCount()) $this->output->put($e->getCount().") ");

        // skip test
        // Sample Message: create user in CreateUserCept.php is not ready for release
        if ($fail instanceof \PHPUnit_Framework_SkippedTest or $fail instanceof \PHPUnit_Framework_IncompleteTest) {
            if ($feature) $this->output->put("[[$feature]] in ");
            $this->output->put($failedTest->getFilename());
            if ($failToString) $this->output->put(" is ".$failToString);
            $this->output->writeln("\n");
            return;
        }

        if ($feature) $this->output->put("Couldn't [[$feature]] in ");
        $this->output->writeln('(('.$failedTest->getFilename().'))');

        $trace = array_reverse($failedTest->getTrace());
        $length = $i = count($trace);
        $last = array_shift($trace);
        if (!method_exists($last, 'getHumanizedAction')) {
            $this->printException($fail);
            return;
        }
        $action = $last->getHumanizedAction();
        if (strpos($action, "am") === 0) {
            $action = 'become' . substr($action, 2);
        }

        // it's exception
        if (!($fail instanceof \PHPUnit_Framework_AssertionFailedError)) {
            $this->printException($fail);
            return;
        };

        // it's assertion
        if (strpos($action, "don't") === 0) {
            $action = substr($action, 6);
            $this->output->writeln("Ups, I unexpectedly managed to $action:\n$failToString");
        } else {
            $this->output->writeln("Ups, I couldn't $action,\n$failToString");
        }


        $this->output->writeln("Scenario Steps:");
        $this->output->writeln("$i. (!$last!)");
        foreach ($trace as $step) {
            $i--;
            $this->output->writeln("$i. " . $step);
            if (($length - $i - 1) >= $this->traceLength) break;
        }
        if ($this->debug) {
            $this->printException($fail);
        }

    }

    public function printException(\Exception $e)
    {
        $this->output->writeln("\n(!".get_class($e).': '.$e->getMessage()."!)\n");
        $i = 0;
        foreach ($e->getTrace() as $step) {
            $i++;
            if (!isset($step['file'])) continue;
            $step['file'] = $this->highlightLocalFiles($step['file']);

            $this->output->writeln(sprintf("#%d %s(%s)",
                $i,
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
        $this->output->writeln("");
    }

    private function highlightLocalFiles($file)
    {
        if (strpos($file, \Codeception\Configuration::projectDir()) === 0) {
            if (strpos($file, \Codeception\Configuration::projectDir() . 'codecept.phar') === 0) {
                return $file;
            }
            if (strpos($file, \Codeception\Configuration::projectDir() . 'vendor') === 0) {
                return $file;
            }
            return "((".$file."))";
        }
        return $file;
    }

    static function getSubscribedEvents()
    {
        return array(
            'suite.before' => 'beforeSuite',
            'suite.after' => 'afterSuite',
            'test.before' => 'before',
            'test.after' => 'afterTest',
            'test.start' => 'startTest',
            'test.end' => 'endTest',
            'step.before' => 'beforeStep',
            'step.after' => 'afterStep',
            'test.success' => 'testSuccess',
            'test.fail' => 'testFail',
            'test.error' => 'testError',
            'test.incomplete' => 'testIncomplete',
            'test.skipped' => 'testSkipped',
            'test.fail.print' => 'printFail',
        );
    }


}
