# Codeception Core

The most important classes are defined in root of Codeception.

* `Codecept` - starts event dispatcher, loads subscribers, starts SuiteManager
* `SuiteManager` - starts modules, starts test runner
* `TestLoader` - loads tests from files
* `Configuration` - loads YAML configuration
* `Events` - defines all Codeception events
* `TestCase` - applies Codeception feature to `PHPUnit\Framework\TestCase` class.