# Codeception Core

The most important classes are defined in root of Codeception.

* `Codecept` - starts event dispatcher, loads subscribers, starts SuiteManager
* `SuiteManager` - starts modules, starts test runner
* `Test\Loader` - loads tests from files
* `Configuration` - loads YAML configuration
* `Events` - defines all Codeception events
* `Test\Unit` - base class for unit tests, applies Codeception features to `PHPUnit\Framework\TestCase` class.