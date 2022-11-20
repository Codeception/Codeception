#### 5.0.5

* Don't disable test shuffling when --shard option is used (#6605)
* Provided typed signatures for attributes (#6600) by @mdoelker
* Support for Attributes in generated Actions files (#6593) by @yesdevnull
* Fixed expectNotToPerformAssertions in unit tests (#6602) by @yesdevnull

#### 5.0.4

* Execute FailFast subscriber before module _failed hooks #6586 by @yesdevnull
* Fixed parsing of @skip annotation #6596
* Undeprecated untyped method arguments in Cest format #6591
* Removed unnecessary overrides of $resultAggregator property in Unit format and TestCaseWrapper #6590
* Print failure/error/warning/skipped/incomplete messages in HTML reports #6595
* Fixed counting of successful tests #6595

#### 5.0.3

* Fixed passing test result to dependent tests in unit tests (#6580)
* Fixed `TypeError` when @coversNothing annotation is used by Slamdunk (#6582)
* `codecept init unit` creates `tests/Support` directory (#6578)
* Fixed phar file url in `self-update` command by @voku (#6563)
* Added message how to exit Codeception console by @ThomasLandauer (#6561)
* Improved compatibility with PHPUnit 10

#### 5.0.2

* Fixed remote code coverage for namespaced suites (#6533)
* Fixed data provider in abstract Cest class @p-golovin (#6560)
* Fixed issue when include groups and test groups empty @geega (#6557)

#### 5.0.1

* Propagate --ext and --override parameters to included test suites by @calvinalkan (#6536)
* Fixed false negative message about stecman/symfony-console-completion package by @geega (#6541)
* Fixed a number of issues in template functionality (#6552)
* Fixed DataProvider, to properly load dataProviders from abstract classes by @Basster (#6549)

#### 5.0.0

Summary of all differences from Codeception 4

##### Added

* Basic attribute support
* `--shard`, `--grep`, `--filter` options
* Test can be filtered by data provider case number or name
* Tests of all formats are reported as useless if they perform no assertions and `reports_useless_tests` setting is enabled
* Array of variables can be passed to all `pause` functions/methods
* Dynamic configuration with parameters can use arrays and other non-string types (#6409)
* `codecept_pause` function and `$this->pause()` in unit tests (#6387)
* Interactive console is executed in the scope of paused test class.
* New code coverage settings:
  - `path_coverage` - enables path and branch coverage
  - `strict_covers_annotation` - marks test as risky if it has `@covers` annotation but executes some other code
  - `ignore_deprecated_code` - doesn't collect code coverage for code having `@deprecated` annotation
  - `disable_code_coverage_ignore` - ignores `@codeCoverageIgnore`, `@codeCoverageIgnoreStart` and `@codeCoverageIgnoreEnd` annotations
* Optional value to `fail-fast` option
* Dynamic configuration with parameters can use arrays and other non-string types

##### Changed

* PHPUnit is no longer the engine of Codeception, but TestCase format is still supported and assertions are still used
* Generators create namespaced test suites by default
* Replaced Hoa Console with PsySH in `codecept console`
* Used Symfony VarDumper in `codecept_debug`
* Fixed DotReporter output format
* Module `after` and `failed` hooks are executed in reverse order
* Introduced strict typing and other features of PHP 7 and 8.
* Cest format can use data providers from other classes
* Fixed injecting dependencies to actor in Cest and Gherkin formats #6506
* Variadic parameters can be skipped in dependency injection #6505
* Deprecated untyped method arguments in Cest format #6521

##### Removed

* `JSON` and `TAP` report formats
* Code coverage blacklist functionality
* `generate:cept` command (`Cept` format itself is deprecated)
* Deprecated class aliases:
  - Codeception\TestCase\Test
  - Codeception\Platform\Group
  - Codeception\Platform\Group
  - Codeception\TestCase
* Settings
  - `log_incomplete_skipped`
  - `paths.log` (replaced by `paths.output`)
  - Suite setting `class_name` (replaced by `actor`)
  - Global setting `actor` (replaced by `actor_prefix`)
* `Configuration::logDir` method (replaced by `Configuration::outputDir` in 2.0)
* Custom reporters implementing TestListener are no longer supported and must be converted to Extensions

##### Supported versions

* PHP 8
* PHPUnit 9 (prepared for upcoming PHPUnit 10)
* Symfony 4.4 - 6.x

#### 5.0.0-RC8

* Deprecated untyped method arguments in Cest format #6521
* Improved code style of generated files #6522
* Removed "Powered by PHPUnit" message #6520

#### 5.0.0-RC7

* Fixed injecting dependencies to actor in Cest and Gherkin formats #6506
* Variadic parameters can be skipped in dependency injection #6505

#### 5.0.0-RC6

* Added new attributes (Prepare, Env, BeforeClass,AfterClass, Given, When, Then)
* Class level attributes are applied to all methods
* Codeception attributes are supported in unit tests
* Cest format can use data providers from other classes

#### 5.0.0-RC5

* Substitute invalid UTF-8 characters in debug and step output by @SamoylenkoSU (#6483)

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
