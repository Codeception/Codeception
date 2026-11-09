<?php

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Extension;

/**
 * Reports how many arguments of the finished tests' steps still hold objects passed by the tests.
 */
class StepArgumentsReporter extends Extension
{
    /** @var array */
    public static $events = [
        Events::SUITE_AFTER => 'afterSuite',
    ];

    public function afterSuite(SuiteEvent $e): void
    {
        $count = 0;
        foreach ($e->getSuite()->getTests() as $test) {
            foreach ($test->getScenario()->getSteps() as $step) {
                foreach ($step->getArguments() as $argument) {
                    if ($argument instanceof ArrayObject) {
                        ++$count;
                    }
                }
            }
        }
        $this->writeln("Step arguments holding objects: {$count}");
    }
}
