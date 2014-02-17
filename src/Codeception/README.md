# Codeception Core

The most important classes are defined in root of Codeception.

* `Codecept` - starts event dispatcher, loads subscribers, starts SuiteManager
* `SuiteManager` - loads test files, loads modules, starts test runner
* `Configuration` - loads YAML configuration
* `Events` - defines all Codeception events
* `TestCase` - applies Codeception feature to `PHPUnit_Framework_TestCase` class.