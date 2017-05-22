# Official Extensions

## Codeception\Extension\Logger

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




## Codeception\Extension\Recorder

Saves a screenshot of each step in acceptance tests and shows them as a slideshow on one HTML page.
Activated only for suites with WebDriver module enabled.

The screenshots are saved to `tests/_output/record_*` directories, open `index.html` to see them as a slideshow.

#### Installation

Add this to the list of enabled extensions in your `codeception.yml`:

``` yaml
extensions:
    enabled:
        - Codeception\Extension\Recorder
```

#### Configuration

* `delete_successful` (default: true) - delete screenshots for successfully passed tests (i.e. log only failed and errored tests)
* `module` (default: WebDriver) - which module for screenshots to use. Set `AngularJS` if you want to use it with AngularJS module. Generally, the module should implement `Codeception\Lib\Interfaces\ScreenshotSaver` interface.


#### Examples:

``` yaml
extensions:
    enabled:
        Codeception\Extension\Recorder:
            module: AngularJS # enable for Angular
            delete_successful: false # keep screenshots of successful tests
```




## Codeception\Extension\RunFailed

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



## Codeception\Extension\SimpleOutput

This extension demonstrates how you can implement console output of your own.
Recommended to be used for development purposes only.



