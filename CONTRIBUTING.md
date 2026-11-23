# How to contribute

At Codeception we are glad to receive contributions and patches from the community. There are a few guidelines that we need contributors to follow so that we can have a chance of keeping on top of things.

1. [Fork](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/working-with-forks/fork-a-repo) the repository, clone your fork and add the main repository as `upstream` remote:
   `git remote add upstream git@github.com:Codeception/Codeception.git`
2. Before starting new work, update your local `main`: `git pull upstream main`
3. Create a new branch based on `main` for each bug fix or feature
4. Make your changes and add tests for them (see [Tests](#tests))
5. Push the branch to your fork and open a pull request against `main`. Each pull request should contain a single change.

Bigger changes should have an issue, so they can be discussed before you spend time on them. If there already is one, leave a comment that you are working on it. Small changes and documentation fixes don't need an issue.

## Coding Standards
All contributions must follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard.
Check it with `composer cs-prod` and `composer cs-tests` before sending a pull request.

## Code
**All pull requests should be sent to the `main` branch.**

## Tests

Check the [tests/README.md](tests/README.md) to learn how to run and write internal tests.
We encourage you to write a test for a patch you are implementing. If this doesn't seem possible, such PRs are still valid and can be accepted.

We also encourage you to submit bug reports with a failing test or test environment (3rd party repo with Codeception installation) with demonstration of a failure. That makes it easier for us to find the cause and fix it.

## Documentation

### Guides
If you want to contribute documentation to the guides you are asked to send your changes to the website repository: [codeception.github.com](https://github.com/Codeception/codeception.github.com/tree/master/docs)

### Modules Documentation
The documentation for each module is directly generated from the corresponding docblock which can be found in each module (src/Codeception/Module/*.php in corresponding codeception/module-NAME repository).
