# How to contribute

At Codeception we are glad to receive contributions and patches from the community. There are a few guidelines that we need contributors to follow so that we can have a chance of keeping on top of things.

Please check the guide for sending your contributions with Github at
https://github.com/Codeception/Codeception/wiki/Git-workflow-for-Codeception-contributors

## Coding Standards
All contributions must follow [PSR-2](http://www.php-fig.org/psr/psr-2/) coding standard.

## Code
**Bugfixes should be sent to to current stable branch, which is the same as major version number.**

Breaking features and major improvements should be sent into `master`. When you send PRs to master, they will be added to release cycle only when the next stable branch is started.

## Tests

Check the [tests/README.md](tests/README.md) to learn how to run and write internal tests.
We encourage you to write a test for a patch you are implementing. If this doesn't seem possible, such PRs are stil valid and can be accepted.

We also encourage to submit bug reports with a failing test or test environment (3rd party repo with Codeception installation) with demonstration of a failure. That makes easier to us to find the cause and fix it.

## Documentation

### Guides
If you want to contribute documentation to the guides you are asked to send your changes to the /docs/ folder: https://github.com/Codeception/Codeception/tree/2.2/docs. Theses files are the source for the codeception website guides: http://codeception.com/docs/01-Introduction. Remind to send your documentation improvements to the right "repository branch" depending on the Codeception version you are working with: 2.2, master,...

### Modules Documentation
The documentation for each module is directly generated from the corresponding docblock which can be found in each module (src/Codeception/Module/*.php).
