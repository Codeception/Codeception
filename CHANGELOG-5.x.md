#### 5.0.0-RC4

* Implemented basic attribute support (#6449) by @DavertMik
* Significantly reduced dependencies on PHPUnit
* Replaced PHPUnit\Framework\TestResult with ResultAggregator
* Added assertionCount method to ResultAggregator 
* DotReporter prints standard result summary (#6441) by @Orchestrator404
* Fixed DotReporter output format
* Fixed fetching remote code coverage data (#6448)
* Loading .env file must not override existing environment variables (#6477)
* All changes from 4.2.0 and 4.2.1:
  * Improved multi-application experience, allow filtering suites by name (#6435) by @calvinalkan
  * Configuration override is passed to included suites (#5978) by @calvinalkan
  * Made dry-run command work with module methods having return types (#6470)
  * Support for expectError/Warning/Notice/Deprecation methods in unit tests (Requires PHPUnit 8.4+)
  * Implemented new setting `convert_deprecations_to_exceptions` (#6469)
  * Action file generator: Do not return when return type is never (#6462)
  * Execute setupBeforeClass/tearDownAfterClass only once (#6481)

#### 5.0.0-RC3

* Fix incorrect type declaration in InitTemplate by @ziadoz
* Stricter check for phpdotenv v5 (older versions are not supported)
* Throw exception if actor setting is missing in suite configuration
* Use correct types in ParamsLoader and Configuration classes, avoid type errors

#### 5.0.0-RC2

* Added `--shard`, `--grep`, `--filter` options (#6399)
* Added new code coverage settings (#6423)
* Dynamic configuration with parameters can use arrays and other non-string types (#6409)
* Introduced `codecept_pause` function and `$this->pause()` in unit tests (#6387)
* Interactive console is executed in the scope of paused test class.
* Array of variables can be passed to all `pause` functions/methods
* Replaced Hoa Console with PsySH in `codecept console`
* Used Symfony VarDumper in `codecept_debug` (#6406)
* Fixed type error in code coverage exclude filter by @W0rma
* Fix type error in Recorder extension

#### 5.0.0-RC1

* Use PHPUnit 9 until PHPUnit 10 is released

#### 5.0.0-alpha3

* Support intersection types in actions
* Introduced PSR-12 code style
* Extracted some code to modules and shared libs
* Fixed new incompatibilities with PHPUnit 10

#### 5.0.0-alpha2

* Generators create namespaced test suites by default (#6071) by @DavertMik
* Test can be filtered by data provider case number or name (#6363) by @Naktibalda
* Removed `generate:cept` command (Cept format is deprecated)
* Removed settings `disallow_test_output` and `log_incomplete_skipped`.
* Removed setting `paths.log` (it was replaced by `paths.output` in Codeception 2.3)
* Removed suite setting `class_name` (replaced by `actor` in Codeception 2.3)
* Removed global setting `actor` (replaced by `actor_prefix` in Codeception 2.3)
* Removed `Configuration::logDir` method (replaced by `Configuration::outputDir` in 2.0)
* ParamLoader throws exception if xml file is used but simplexml extension is missing (#6346) by @mdoelker
* Updated codebase to use PHP 8.0 features by @TavoNiievez
* Don't report test as useless if it failed (fixed bug introduced in alpha1)
* Don't report local test coverage for remote suites (fixed bug introduced in alpha1)
* Moved XmlBuilder class to module-soap

#### 5.0.0-alpha1

* Decoupled test execution and reporting from PHPUnit
* Custom reporters implementing TestListener are no longer supported and must be converted to Extensions
* Tests of all formats are reported as useless if they perform no assertions and `reports_useless_tests` setting is enabled
* Added path_coverage setting to enable path and branch coverage #6158 by @s0lus
* Added optional value to `fail-fast` option (#6275) by #Verest
* Module after and failed hooks are executed in reverse order (#6062) by WillemHoman
* Removed `JSON` and `TAP` loggers
* Removed code coverage blacklist functionality
* Removed deprecated class aliases
  - Codeception\TestCase\Test
  - Codeception\Platform\Group
  - Codeception\Platform\Group
  - Codeception\TestCase
* Introduced strict types in the code base.
* Compatible with PHPUnit 10 only
* Compatible with Symfony 4.4 - 6.0
* Requires PHP 8.0 or higher
