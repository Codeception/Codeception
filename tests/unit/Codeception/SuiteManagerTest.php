<?php

class SuiteManagerTest extends PHPUnit_Framework_TestCase
{

    public function testAddModule() {

        $testmodule = 'Codeception\Module\EmulateModuleHelper';

        \Codeception\SuiteManager::addModule($testmodule);
        $this->assertContains($testmodule, array_keys(Codeception\SuiteManager::$modules));
        $this->assertInstanceOf($testmodule, \Codeception\SuiteManager::$modules[$testmodule]);

        \Codeception\SuiteManager::removeModule($testmodule);

        \Codeception\SuiteManager::addModule(new $testmodule);
        $this->assertContains($testmodule, array_keys(\Codeception\SuiteManager::$modules));
    }
    
    public function testInitializeModules() {
        $testmodule = 'Codeception\Module\EmulateModuleHelper';
        \Codeception\SuiteManager::addModule($testmodule);

        \Codeception\SuiteManager::initializeModules();

        $this->assertContains('emptyAction',array_keys(\Codeception\SuiteManager::$methods));
        $this->assertEquals($testmodule, \Codeception\SuiteManager::$methods['emptyAction']);

    }
    
}
