# Official Extensions

## DotReporter

[See Source](https://github.com/Codeception/Codeception/blob/2.3/ext/DotReporter.php)

DotReporter provides less verbose output for test execution.
Like PHPUnit printer it prints dots "." for successful testes and "F" for failures.

![](https://cloud.githubusercontent.com/assets/220264/26132800/4d23f336-3aab-11e7-81ba-2896a4c623d2.png)

```bash
 ..........
 ..........
 ..........
 ..........
 ..........
 ..........
 ..........
 ..........

Time: 2.07 seconds, Memory: 20.00MB

OK (80 tests, 124 assertions)
```


Enable this reporter with `--ext option`

```
codecept run --ext DotReporter
```

Failures and Errors are printed by a standard Codeception reporter.
Use this extension as an example for building custom reporters.



## Logger

[See Source](https://github.com/Codeception/Codeception/blob/2.3/ext/Logger.php)

Log suites/tests/steps using Monolog library.
Monolog should be installed additionally by Composer.

```
composer require monolog/monolog
```

Steps are logged into `tests/_output/codeception.log`

To enable this module add to your `codeception.yml`:

``` yaml
extensions:
    enabled: [Codeception\Extension\Logger]
```

#### Config

* `max_files` (default: 3) - how many log files to keep




## Recorder

[See Source](https://github.com/Codeception/Codeception/blob/2.3/ext/Recorder.php)

Saves a screenshot of each step in acceptance tests and shows them as a slideshow on one HTML page (here's an [example](http://codeception.com/images/recorder.gif))
Activated only for suites with WebDriver module enabled.

The screenshots are saved to `tests/_output/record_*` directories, open `index.html` to see them as a slideshow.

#### Installation

Add this to the list of enabled extensions in `codeception.yml` or `acceptance.suite.yml`:

``` yaml
extensions:
    enabled:
        - Codeception\Extension\Recorder
```

#### Configuration

* `delete_successful` (default: true) - delete screenshots for successfully passed tests  (i.e. log only failed and errored tests).
* `module` (default: WebDriver) - which module for screenshots to use. Set `AngularJS` if you want to use it with AngularJS module. Generally, the module should implement `Codeception\Lib\Interfaces\ScreenshotSaver` interface.


#### Examples:

``` yaml
extensions:
    enabled:
        Codeception\Extension\Recorder:
            module: AngularJS # enable for Angular
            delete_successful: false # keep screenshots of successful tests
```




## RunFailed

[See Source](https://github.com/Codeception/Codeception/blob/2.3/ext/RunFailed.php)

Saves failed tests into tests/log/failed in order to rerun failed tests.

To rerun failed tests just run the `failed` group:

```
php codecept run -g failed
```

Starting from Codeception 2.1 **this extension is enabled by default**.

``` yaml
extensions:
    enabled: [Codeception\Extension\RunFailed]
```

On each execution failed tests are logged and saved into `tests/_output/failed` file.



## SimpleReporter

[See Source](https://github.com/Codeception/Codeception/blob/2.3/ext/SimpleReporter.php)

This extension demonstrates how you can implement console output of your own.
Recommended to be used for development purposes only.



