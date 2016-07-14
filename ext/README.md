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

Saves screenshots of each step in acceptance tests and shows them as a slideshow.
Activated only for suites with WebDriver module enabled.

 ![recorder](http://codeception.com/images/recorder.gif)

Slideshows saves are saved into `tests/_output/record_*` directories.
Open `index.html` to see the slideshow.

#### Installation

Add to list of enabled extensions

``` yaml
extensions:
    enabled: [Codeception\Extension\Recorder]
```

#### Configuration

* `delete_successful` (default: true) - delete records for successfully passed tests (log only failed and errored)
* `module` (default: WebDriver) - which module for screenshots to use.
Module should implement `Codeception\Lib\Interfaces\ScreenshotSaver` interface.
Currently only WebDriver or any its children can be used.

``` yaml
extensions:
    config:
        Codeception\Extension\Recorder:
            delete_successful: false
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



