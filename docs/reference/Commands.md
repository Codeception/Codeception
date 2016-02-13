# Console Commands

## GenerateSuite

Create new test suite. Requires suite name and actor name

* ``
* `codecept g:suite api` -> api + ApiTester
* `codecept g:suite integration Code` -> integration + CodeTester
* `codecept g:suite frontend Front` -> frontend + FrontTester




## Console

Try to execute test commands in run-time. You may try commands before writing the test.

* `codecept console acceptance` - starts acceptance suite environment. If you use WebDriver you can manipulate browser with Codeception commands.



## GenerateGroup

Creates empty GroupObject - extension which handles all group events.

* `codecept g:group Admin`



## GenerateCept

Generates Cept (scenario-driven test) file:

* `codecept generate:cept suite Login`
* `codecept g:cept suite subdir/subdir/testnameCept.php`
* `codecept g:cept suite LoginCept -c path/to/project`




## Run

Executes tests.

```
Arguments:
 suite                 suite to be tested
 test                  test to be run

Options:
 --config (-c)         Use custom path for config
 --report              Show output in compact style
 --html                Generate html with results (default: "report.html")
 --xml                 Generate JUnit XML Log (default: "report.xml")
 --tap                 Generate Tap Log (default: "report.tap.log")
 --json                Generate Json Log (default: "report.json")
 --colors              Use colors in output
 --no-colors           Force no colors in output (useful to override config file)
 --silent              Only outputs suite names and final results
 --steps               Show steps in output
 --debug (-d)          Show debug and scenario output
 --coverage            Run with code coverage (default: "coverage.serialized")
 --coverage-html       Generate CodeCoverage HTML report in path (default: "coverage")
 --coverage-xml        Generate CodeCoverage XML report in file (default: "coverage.xml")
 --coverage-text       Generate CodeCoverage text report in file (default: "coverage.txt")
 --no-exit             Don't finish with exit code
 --group (-g)          Groups of tests to be executed (multiple values allowed)
 --skip (-s)           Skip selected suites (multiple values allowed)
 --skip-group (-sg)    Skip selected groups (multiple values allowed)
 --env                 Run tests in selected environments. (multiple values allowed, environments can be merged with ',')
 --fail-fast (-f)      Stop after first failure
 --help (-h)           Display this help message.
 --quiet (-q)          Do not output any message.
 --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 --version (-V)        Display this application version.
 --ansi                Force ANSI output.
 --no-ansi             Disable ANSI output.
 --no-interaction (-n) Do not ask any interactive question.
```




## SelfUpdate

Auto-updates phar archive from official site: 'http://codeception.com/codecept.phar' .

* `php codecept.phar self-update`

@author Franck Cassedanne <franck@cassedanne.com>



## GenerateTest

Generates skeleton for Unit Test that extends `Codeception\TestCase\Test`.

* `codecept g:test unit User`
* `codecept g:test unit "App\User"`



## Build

Generates Actor classes (initially Guy classes) from suite configs.
Starting from Codeception 2.0 actor classes are auto-generated. Use this command to generate them manually.

* `codecept build`
* `codecept build path/to/project`




## GenerateHelper

Creates empty Helper class.

* `codecept g:helper MyHelper`
* `codecept g:helper "My\Helper"`




## Bootstrap

Creates default config, tests directory and sample suites for current project. Use this command to start building a test suite.

By default it will create 3 suites **acceptance**, **functional**, and **unit**.

* `codecept bootstrap` - creates `tests` dir and `codeception.yml` in current dir.
* `codecept bootstrap --empty` - creates `tests` dir without suites
* `codecept bootstrap --namespace Frontend` - creates tests, and use `Frontend` namespace for actor classes and helpers.
* `codecept bootstrap --actor Wizard` - sets actor as Wizard, to have `TestWizard` actor in tests.
* `codecept bootstrap path/to/the/project` - provide different path to a project, where tests should be placed




## GenerateEnvironment

Generates empty environment configuration file into envs dir:

 * `codecept g:env firefox`

Required to have `envs` path to be specifed in `codeception.yml`



## GeneratePhpUnit

Generates skeleton for unit test as in classical PHPUnit.

* `codecept g:phpunit unit UserTest`
* `codecept g:phpunit unit User`
* `codecept g:phpunit unit "App\User`




## GenerateScenarios

Generates user-friendly text scenarios from scenario-driven tests (Cest, Cept).

* `codecept g:scenarios acceptance` - for all acceptance tests
* `codecept g:scenarios acceptance --format html` - in html format
* `codecept g:scenarios acceptance --path doc` - generate scenarios to `doc` dir



## GenerateStepObject

Generates StepObject class. You will be asked for steps you want to implement.

* `codecept g:step acceptance AdminSteps`
* `codecept g:step acceptance UserSteps --silent` - skip action questions




## Clean

Cleans `output` directory

* `codecept clean`
* `codecept clean -c path/to/project`




## GenerateCest

Generates Cest (scenario-driven object-oriented test) file:

* `codecept generate:cest suite Login`
* `codecept g:cest suite subdir/subdir/testnameCest.php`
* `codecept g:cest suite LoginCest -c path/to/project`
* `codecept g:cest "App\Login"`




## GeneratePageObject

Generates PageObject. Can be generated either globally, or just for one suite.
If PageObject is generated globally it will act as UIMap, without any logic in it.

* `codecept g:page Login`
* `codecept g:page Registration`
* `codecept g:page acceptance Login`



