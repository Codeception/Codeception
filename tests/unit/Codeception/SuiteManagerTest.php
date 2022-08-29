<?php

declare(strict_types=1);

use Codeception\Attribute\Group;
use Codeception\PHPUnit\TestCase;
use Codeception\ResultAggregator;
use Codeception\SuiteManager;
use Symfony\Component\EventDispatcher\EventDispatcher;

if (!defined('PHPUNIT_TESTSUITE')) {
    define('PHPUNIT_TESTSUITE', true);
}

#[Group('core')]
final class SuiteManagerTest extends TestCase
{
    protected SuiteManager $suiteman;

    protected EventDispatcher $dispatcher;

    public function _setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $settings = \Codeception\Configuration::$defaultSuiteSettings;
        $settings['actor'] = 'CodeGuy';
        $this->suiteman = new SuiteManager($this->dispatcher, 'suite', $settings, []);
    }

    #[Group('core')]
    public function testRun()
    {
        $events = [];
        $eventListener = function ($event, $eventName) use (&$events) {
            $events[] = $eventName;
        };
        $this->dispatcher->addListener('suite.before', $eventListener);
        $this->dispatcher->addListener('suite.after', $eventListener);

        $this->suiteman->run(new ResultAggregator());
        $this->assertSame($events, ['suite.before', 'suite.after']);
    }

    #[Group('core')]
    public function testFewTests()
    {
        if (version_compare(phpversion(), '8.1', '>=') && PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped("Temporary disabled for windows php version 8.1 and greater.");
        }

        $file = \Codeception\Configuration::dataDir() . 'SimpleCest.php';

        $this->suiteman->loadTests($file);
        $this->assertSame(2, $this->suiteman->getSuite()->getTestCount());

        $file = \Codeception\Configuration::dataDir() . 'SimpleWithNoClassCest.php';
        $this->suiteman->loadTests($file);
        $this->assertSame(3, $this->suiteman->getSuite()->getTestCount());
    }

    /**
     * When running multiple environments, getClassesFromFile() method in SuiteManager is called once for each env.
     * See \Codeception\Codecept::runSuite() - for each env new SuiteManager is created and tests loaded.
     * Make sure that calling getClassesFromFile() multiple times will always return the same classes.
     */
    #[Group('core')]
    public function testAddCestWithEnv()
    {
        if (version_compare(phpversion(), '8.1', '>=') && PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped("Temporary disabled for windows php version 8.1 and greater.");
        }

        $file = \Codeception\Configuration::dataDir() . 'SimpleNamespacedTest.php';
        $this->suiteman->loadTests($file);
        $this->assertSame(3, $this->suiteman->getSuite()->getTestCount());
        $newSuiteMan = new SuiteManager(
            $this->dispatcher,
            'suite',
            \Codeception\Configuration::$defaultSuiteSettings,
            [],
        );
        $newSuiteMan->loadTests($file);
        $this->assertSame(3, $newSuiteMan->getSuite()->getTestCount());
    }

    public function testDependencyResolution()
    {
        $this->suiteman->loadTests(codecept_data_dir() . 'SimpleWithDependencyInjectionCest.php');
        $this->assertSame(3, $this->suiteman->getSuite()->getTestCount());
    }

    public function testGroupEventsAreFired()
    {
        $events = [];
        $eventListener = function ($event, $eventName) use (&$events) {
            $events[] = $eventName;
        };
        $this->dispatcher->addListener('test.before', $eventListener);
        $this->dispatcher->addListener('test.before.admin', $eventListener);
        $this->dispatcher->addListener('test.after', $eventListener);
        $this->dispatcher->addListener('test.after.admin', $eventListener);

        $this->suiteman->loadTests(codecept_data_dir() . 'SimpleAdminGroupCest.php');
        $this->suiteman->run(new ResultAggregator());
        $this->assertContains('test.before', $events);
        $this->assertContains('test.before.admin', $events);
        $this->assertContains('test.after', $events);
        $this->assertContains('test.after.admin', $events);
    }
}
