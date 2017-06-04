# IDE Integration

Several major IDE's feature a Codeception integration.

## NetBeans 8.2

### What You'll Get

* Run all tests (i.e. `codecept run`) from NetBeans
* Run the currently focused test method. It is not possible to run just one suite (unit, functional, acceptance) or just one test file.
* View the test results in NetBeans ("Test Results" window and "Output" window)
* In "Projects" window, test files are displayed in a folder "Test Files" (i.e. moved outside of "Source Files")

### Setup

In "Projects" window, right-click on your project > `Properties` > `Testing`, then:

1. `Add Folder...` and select all folders containing test files. Each of these will be (visually) moved into a separate "Test Files" folder in "Projets" window.
1. Check `Codeception`
1. `Codeception` > `Use Custom Codecept File`: Enter the path to your Codeception executable (`codecept`, `codecept.bar` or `codecept.phar`).  
If you have Codeception installed globally on your machine, you may instead provide the path in `Tools` > `Options` > `PHP` > `Frameworks & Tools` > `Codeception` > `Codecept File`.
1. `Codeception` > `Use Custom codeception.yml`: Enter the path to your `codeception.yml`
1. `Codeception` > `Ask for Additional Parameters Before Running Tests`: If you check this, a dialogue box will appear every time you run the tests, allowing you to enter additional [Codeception command-line parameters](http://codeception.com/docs/reference/Commands#Run)

### Now You Can

* Start all tests with `Run` > `Test Project` (or `Alt+F6`).
* In a test file, right-click in a public function and select `Run Focused Test Method`.

**Caution:** There's a bug in "Test Results" window: If you run acceptance tests and Selenium server is not started, the resulting `ConnectionException` isn't reported correctly.
Result: All acceptance tests are silenty skipped and no error is shown, even though in fact no test was executed!
See [NetBeans Bug 270802 Codeception 'ConnectionException's not showing up in 'Test Results' window](https://netbeans.org/bugzilla/show_bug.cgi?id=270802)
