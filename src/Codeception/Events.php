<?php

namespace Codeception;

/**
 * Contains all events dispatched by Codeception.
 *
 * @author tiger-seo <tiger.seo@gmail.com>
 */
final class Events
{
    /**
     * Private constructor. This class cannot be instantiated.
     */
    private function __construct()
    {
    }


    /**
     * The <b>SUITE_INIT</b> event occurs when suite is initialized.
     * Modules are initialized, but Guy class is not loaded.
     *
     * The event listener method receives a {@link Codeception\Event\SuiteEvent} instance.
     */
    const SUITE_INIT = 'suite.init';

    /**
     * The <b>SUITE_BEFORE</b> event occurs before suite is executed.
     *
     * The event listener method receives a {@link Codeception\Event\SuiteEvent} instance.
     */
    const SUITE_BEFORE = 'suite.before';

    /**
     * The <b>SUITE_AFTER</b> event occurs after suite has been executed.
     *
     * The event listener method receives a {@link Codeception\Event\SuiteEvent} instance.
     */
    const SUITE_AFTER = 'suite.after';

    /**
     * The event listener method receives a {@link Codeception\Event\TestEvent} instance.
     */
    const TEST_START = 'test.start';

    /**
     * The event listener method receives a {@link Codeception\Event\TestEvent} instance.
     */
    const TEST_BEFORE = 'test.before';

    /**
     * The event listener method receives a {@link Codeception\Event\StepEvent} instance.
     */
    const STEP_BEFORE = 'step.before';

    /**
     * The event listener method receives a {@link Codeception\Event\StepEvent} instance.
     */
    const STEP_AFTER = 'step.after';

    /**
     * @deprecated or what ??? can't find any usage
     */
    const STEP_FAIL = 'step.fail';

    /**
     * The <b>TEST_FAIL</b> event occurs whenever test has failed.
     *
     * The event listener method receives a {@link Codeception\Event\FailEvent} instance.
     */
    const TEST_FAIL = 'test.fail';

    /**
     * The <b>TEST_ERROR</b> event occurs whenever test got an error while being executed.
     *
     * The event listener method receives a {@link Codeception\Event\FailEvent} instance.
     */
    const TEST_ERROR = 'test.error';

    /**
     * The event listener method receives a {@link Codeception\Event\TestEvent} instance.
     */
    const TEST_PARSED = 'test.parsed';

    /**
     * The event listener method receives a {@link Codeception\Event\FailEvent} instance.
     */
    const TEST_INCOMPLETE = 'test.incomplete';

    /**
     * The event listener method receives a {@link Codeception\Event\FailEvent} instance.
     */
    const TEST_SKIPPED = 'test.skipped';

    /**
     * The event listener method receives a {@link Codeception\Event\TestEvent} instance.
     */
    const TEST_SUCCESS = 'test.success';

    /**
     * The event listener method receives a {@link Codeception\Event\TestEvent} instance.
     */
    const TEST_AFTER = 'test.after';

    /**
     * The event listener method receives a {@link Codeception\Event\TestEvent} instance.
     */
    const TEST_END = 'test.end';

    /**
     * The event listener method receives a {@link Codeception\Event\FailEvent} instance.
     */
    const TEST_FAIL_PRINT = 'test.fail.print';

    /**
     * The event listener method receives a {@link Codeception\Event\PrintResultEvent} instance.
     */
    const RESULT_PRINT_AFTER = 'result.print.after';
}
