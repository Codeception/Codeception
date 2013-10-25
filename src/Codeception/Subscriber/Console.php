<?php
namespace Codeception\Subscriber;

use Codeception\Exception\ConditionalAssertionFailed;
use Codeception\SuiteManager;
use Codeception\TestCase\ScenarioDriven;
use Codeception\TestCase;
use Codeception\Util\Console\Message;
use Codeception\Util\Console\Output;
use Codeception\Util\Debug;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Console implements EventSubscriberInterface
{
    protected $steps = true;
    protected $debug = false;
    protected $color = true;
    protected $silent = false;
    protected $lastTestFailed = false;

    protected $traceLength = 5;

    protected $columns = array(40, 5);

    public function __construct($options)
    {
        $this->debug = $options['debug'] || $options['verbosity'] >= OutputInterface::VERBOSITY_VERY_VERBOSE;
        $this->steps = $this->debug || $options['steps'];
        $this->output = new Output($options);
        if ($this->debug) {
            Debug::setOutput($this->output);
        }
    }

    // triggered for scenario based tests: cept, cest
    public function beforeSuite(\Codeception\Event\Suite $e)
    {
        $this->buildResultsTable($e);

        $this->message("%s Tests (%d) ")
            ->with(ucfirst($e->getSuite()->getName()), count($e->getSuite()->tests()))
            ->style('bold')
            ->width(array_sum($this->columns), '-')
            ->prepend("\n")
            ->writeln();

        $message = $this->message(implode(', ',array_map(function ($module) {
            return $module->getName();
        }, SuiteManager::$modules)));
        $message->style('info')
            ->prepend('Modules: ')
            ->writeln(OutputInterface::VERBOSITY_VERBOSE);
        
        $this->message('')->width(array_sum($this->columns), '-')->writeln(OutputInterface::VERBOSITY_VERBOSE);

    }

    // triggered for all tests
    public function startTest(\Codeception\Event\Test $e)
    {
        $test = $e->getTest();
        if ($test instanceof TestCase) {
            return;
        }

        $this->message($test->toString())
            ->style('focus')
            ->prepend('Running ')
            ->width($this->columns[0])
            ->write();
    }

    public function before(\Codeception\Event\Test $e)
    {
        $test = $e->getTest();
        $filename = $test->getFileName();

        if ($test->getFeature()) {
            $this->message("Trying to <focus>%s</focus> (%s)")
                ->with($test->getFeature(), $filename)
                ->width($this->columns[0])
                ->write();

        } else {
            $this->message("Running <focus>%s</focus>")
                ->with($filename)
                ->width($this->columns[0])
                ->write();
        }

        if ($this->steps && count($e->getTest()->getScenario()->getSteps())) {
            $this->output->writeln("\nScenario:");
        }
    }

    public function afterTest(\Codeception\Event\Test $e)
    {
    }

    public function testSuccess(\Codeception\Event\Test $e)
    {
        if ($this->isDetailed($e->getTest())) {
            $this->message('PASSED')->center(' ')->style('ok')->append("\n")->writeln();
            return;
        }
        $this->message('Ok')->writeln();
    }

    public function endTest(\Codeception\Event\Test $e)
    {
    }

    public function testFail(\Codeception\Event\Fail $e)
    {
        if (!$this->steps && ($e->getFail() instanceof ConditionalAssertionFailed)) {
            $this->message('[F]')->style('error')->prepend(' ')->write();
            return;
        }
        if ($this->isDetailed($e->getTest())) {
            $this->message('FAIL')->center(' ')->style('error')->append("\n")->writeln();
            return;
        }
        $this->message('Fail')->style('error')->writeln();
    }

    public function testError(\Codeception\Event\Fail $e)
    {
        if ($this->isDetailed($e->getTest())) {
            $this->message('ERROR')->center(' ')->style('error')->append("\n")->writeln();
            return;
        }
        $this->message('Error')->style('error')->writeln();
    }

    public function testSkipped(\Codeception\Event\Fail $e)
    {
        $message = $this->message('Skipped');
        if ($this->isDetailed($e->getTest())) {
            $message->apply('strtoupper')->append("\n");
        }
        $message->writeln();
    }

    public function testIncomplete(\Codeception\Event\Fail $e)
    {
        $message = $this->message('Incomplete');
        if ($this->isDetailed($e->getTest())) {
            $message->apply('strtoupper')->append("\n");
        }
        $message->writeln();
    }

    protected function isDetailed($test)
    {
        if (!($test instanceof ScenarioDriven)) {
            return false;
        }
        if (!$this->steps or (!count($test->getScenario()->getSteps()))) {
            return false;
        }
        return true;
    }

    public function beforeStep(\Codeception\Event\Step $e)
    {
        if (!$this->steps) {
            return;
        }
        $this->output->writeln("* " . $e->getStep());
//        if ($this->debug) {
//            $this->message('<debug>');
//        }
        
    }

    public function afterStep(\Codeception\Event\Step $e)
    {
//        if ($this->debug) {
//            $this->message("</debug>\n");
//        }
//        $this->output->writeln(json_encode($e->getStep()->pullDebugOutput()));
//        if ($output = $e->getStep()->pullDebugOutput()) {
//            $this->output->write(implode(', ',$output));
//            $this->output->debug($output);
//        }
    }

    public function afterSuite(\Codeception\Event\Suite $e)
    {
        $this->message()->width(array_sum($this->columns), '-')->writeln();
    }

    public function printFail(\Codeception\Event\Fail $e)
    {
        $failedTest = $e->getTest();
        $fail = $e->getFail();
        $this->output->write($e->getCount() . ") ");

        if ($e->getTest() instanceof ScenarioDriven) {
            $this->printScenarioFail($failedTest, $fail);
            return;
        }

        $failToString = \PHPUnit_Framework_TestFailure::exceptionToString($fail);
        $this->message(get_class($failedTest))
            ->append('::')
            ->append($failedTest->getName())
            ->style('bold')
            ->append("\n")
            ->append($failToString)
            ->writeln();

        $this->printException($fail);
    }

    protected function printScenarioFail(ScenarioDriven $failedTest, $fail)
    {
        $feature = $failedTest->getScenario()->getFeature();
        $failToString = \PHPUnit_Framework_TestFailure::exceptionToString($fail);
        $fail_message = $this->message($failedTest->getFilename())->style('bold');

        if ($fail instanceof \PHPUnit_Framework_SkippedTest or $fail instanceof \PHPUnit_Framework_IncompleteTest) {
            $this->printSkippedTest($feature, $failedTest->getFileName(), $failToString);
            return;
        }
        if ($feature) {
            $fail_message->prepend("Failed to $feature in ");
        }
        $fail_message->writeln();
        $this->printScenarioTrace($failedTest, $failToString);
        if ($this->output->getVerbosity() == OutputInterface::VERBOSITY_DEBUG) {
            $this->printException($fail);
            return;
        }
        if (!$fail instanceof \PHPUnit_Framework_AssertionFailedError) {
            $this->printException($fail);
            return;
        }
    }

    public function printException(\Exception $e)
    {
        static $limit = 10;
        static $bottomCut = -9;
        $this->message("[%s] %s")->with(get_class($e), $e->getMessage())->block('error')->writeln(
            $e instanceof \PHPUnit_Framework_AssertionFailedError
                ? OutputInterface::VERBOSITY_DEBUG
                : OutputInterface::VERBOSITY_NORMAL
        );

        $trace = \PHPUnit_Util_Filter::getFilteredStacktrace($e, false);
        array_splice($trace, $bottomCut);
        $i = 0;
        foreach ($trace as $step) {
            $i++;
            $message = $this->message($i)->prepend('#')->width(4);
            if (!isset($step['file']) && isset($step['class'])) {
                $message->append("[internal] " . $step['class'] . '.' . $step['function']);
            }

            if (isset($step['file'])) {
                $message->append($step['file'] . ':' . $step['line']);
            }
            $message->writeln();

            if ($i >= $limit) {
                break;
            }
        }
    }

    protected function message($text = '')
    {
        return new Message($text, $this->output);
    }

    /**
     * Sample Message: create user in CreateUserCept.php is not ready for release
     *
     * @param $feature
     * @param $fileName
     * @param $failToString
     */
    public function printSkippedTest($feature, $fileName, $failToString)
    {
        $message = $this->message();
        if ($feature) {
            $message->append($feature)->style('focus')->append(' in ');
        }
        $message->append($fileName);
        if ($failToString) {
            $message->append(" is $failToString");
        }
        $message->write(OutputInterface::VERBOSITY_VERBOSE);
    }

    /**
     * @param $action
     * @param $failToString
     */
    public function printFailMessage($action, $failToString)
    {
        if (strpos($action, "don't") === 0) {
            $action = substr($action, 6);
            $this->output->writeln("Sorry, I unexpectedly managed to $action:\n$failToString");
        } else {
            $this->output->writeln("Sorry, I couldn't $action:\n$failToString");
        }
    }

    /**
     * @param $failedTest
     * @param $fail
     */
    public function printScenarioTrace($failedTest, $failToString)
    {
        $trace = array_reverse($failedTest->getTrace());
        $length = $i = count($trace);
        $last = array_shift($trace);
        if (!method_exists($last, 'getHumanizedAction')) {
            return;
        }
        $this->printFailMessage($last->getHumanizedAction(), $failToString);

        $this->output->writeln("Scenario Steps:");
        $this->message($last)->style('error')->prepend("$i. ")->writeln();
        foreach ($trace as $step) {
            $i--;
            $this->message($i)->width(strlen($length))->append(". $step")->writeln();
            if (($length - $i - 1) >= $this->traceLength) {
                break;
            }
        }
        $this->output->writeln("");
    }

    /**
     * @param \Codeception\Event\Suite $e
     */
    protected function buildResultsTable(\Codeception\Event\Suite $e)
    {
        $this->columns = array(40, 5);
        foreach ($e->getSuite()->tests() as $test) {
            if ($test instanceof ScenarioDriven) {
                $this->columns[0] = max(
                    $this->columns[0],
                    20 + strlen($test->getFeature()) + strlen($test->getFileName())
                );
                continue;
            }
            $this->columns[0] = max($this->columns[0], 10 + strlen($test->toString()));
        }
    }

    // events
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
