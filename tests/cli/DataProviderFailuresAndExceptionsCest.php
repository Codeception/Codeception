<?php

declare(strict_types=1);

use Codeception\Attribute\Before;

final class DataProviderFailuresAndExceptionsCest
{
    private function moveToPath(CliGuy $I)
    {
        $I->amInPath('tests/data/dataprovider_failures_and_exceptions');
    }

    /**
     * This looks at only the contents of stdout when there is a failure in parsing a dataProvider annotation.
     * When there is a failure all the useful information should go to stderr, so stdout is left with
     * only the version headers.
     */
    #[Before('moveToPath')]
    public function runTestWithDataProvidersFailureStdout(CliGuy $I)
    {
        /**
         * On windows /dev/null is NUL so detect running OS and return the appropriate string for redirection.
         * As some systems have php_uname and co disabled, we use the DIRECTORY_SEPARATOR constant to
         * figure out if we are running on windows or not.
         */
        $devNull = (DIRECTORY_SEPARATOR === '\\') ? 'NUL' : '/dev/null';
        $I->executeCommand('run -n -v unit DataProvidersFailureCest 2> ' . $devNull, false);
        // We should only see the version headers in stdout when there is this kind of failure.
        $I->canSeeShellOutputMatches('#^Codeception PHP Testing Framework v.+#');
        $I->seeResultCodeIs(1);
    }

    /**
     * This redirects stderr to stdout so that we can test the contents of stderr. Stderr is where all the interesting
     * information should be when there is a failure.
     */
    #[Before('moveToPath')]
    public function runTestWithDataProvidersFailureStderr(CliGuy $I)
    {
        $I->executeCommand('run -n unit DataProvidersFailureCest 2>&1', false);
        $I->seeInShellOutput("DataProvider 'rectangle' for DataProvidersFailureCest::testIsTriangle is invalid or not callable");
        // For Unit tests PHPUnit throws the errors, this confirms that we haven't ended up running PHPUnit test Loader
        $I->dontSeeInShellOutput('The data provider specified for DataProvidersFailureCest::testIsTriangle');
        $I->dontSeeInShellOutput('Method rectangle does not exist');
        $I->dontSeeInShellOutput('FAILURES!');
        $I->dontSeeInShellOutput('WARNINGS!');
        $I->dontSeeInShellOutput('OK');
        $I->dontSeeInShellOutput('Tests: 1, Assertions: 0, Warnings: 1.');
        // In normal mode the Exception trace should not appear.
        $I->dontSeeInShellOutput('Exception trace');
        $I->seeResultCodeIs(1);
    }

    /**
     * This adds the -v to the stderr test which should just add the Exception Trace to the output.
     */
    #[Before('moveToPath')]
    public function runTestWithDataProvidersFailureStderrVerbose(CliGuy $I)
    {
        $I->executeCommand('run -n unit DataProvidersFailureCest -v 2>&1', false);
        $I->seeInShellOutput("DataProvider 'rectangle' for DataProvidersFailureCest::testIsTriangle");
        // For Unit tests PHPUnit throws the errors, this confirms that we haven't ended up running PHPUnit test Loader
        $I->dontSeeInShellOutput('PHPUnit_Framework_Warning');
        $I->dontSeeInShellOutput('The data provider specified for DataProvidersFailureCest::testIsTriangle');
        $I->dontSeeInShellOutput('Method rectangle does not exist');
        $I->dontSeeInShellOutput('FAILURES!');
        $I->dontSeeInShellOutput('WARNINGS!');
        $I->dontSeeInShellOutput('OK');
        $I->dontSeeInShellOutput('Tests: 1, Assertions: 0, Warnings: 1.');
        // In verbose mode the Exception trace should be output.
        $I->seeInShellOutput('[Codeception\Exception\InvalidTestException]');
        $I->seeInShellOutput('Exception trace');
        $I->seeResultCodeIs(1);
    }

    /**
     * This looks at only the contents of stdout when there is an exception thrown when executing a dataProvider
     * function.
     * When exception thrown all the useful information should go to stderr, so stdout is left with nothing.
     */
    #[Before('moveToPath')]
    public function runTestWithDataProvidersExceptionStdout(CliGuy $I)
    {
        /**
         * On windows /dev/null is NUL so detect running OS and return the appropriate string for redirection.
         * As some systems have php_uname and co disabled, we use the DIRECTORY_SEPARATOR constant to
         * figure out if we are running on windows or not.
         */
        $devNull = (DIRECTORY_SEPARATOR === '\\') ? 'NUL' : '/dev/null';
        $I->executeCommand('run -n unit DataProvidersExceptionCest -v 2> ' . $devNull, false);
        // Depending on the test environment, we either see nothing or just the headers here.
        $I->canSeeShellOutputMatches('#^Codeception PHP Testing Framework v.+#');
        $I->seeResultCodeIs(1);
    }

    /**
     * This redirects stderr to stdout so that we can test the contents of stderr. Stderr is where all the interesting
     * information should be when there is a failure.
     */
    #[Before('moveToPath')]
    public function runTestWithDataProvidersExceptionStderr(CliGuy $I)
    {
        $I->executeCommand('run -n unit DataProvidersExceptionCest 2>&1', false);
        // For Unit tests PHPUnit throws the errors, this confirms that we haven't ended up running PHPUnit test Loader
        $I->dontSeeInShellOutput('There was 1 warning');
        $I->dontSeeInShellOutput('PHPUnit_Framework_Warning');
        $I->dontSeeInShellOutput('The data provider specified for DataProvidersExceptionTest::testIsTriangle');
        $I->dontSeeInShellOutput('FAILURES!');
        $I->dontSeeInShellOutput('WARNINGS!');
        $I->dontSeeInShellOutput('OK');
        // We should not see the messages related to a failure to parse the dataProvider function
        $I->dontSeeInShellOutput('[Codeception\Exception\TestParseException]');
        $I->dontSeeInShellOutput("Couldn't parse test");
        $I->dontSeeInShellOutput("DataProvider 'rectangle' for DataProvidersFailureCest::testIsTriangle ");

        // We should just see the message
        $I->seeInShellOutput('Something went wrong!!!');
        // We don't have the verbose flag set, so there should be no trace.
        $I->dontSeeInShellOutput('Exception trace:');
        $I->seeResultCodeIs(1);
    }

    /**
     * This adds the -v to the stderr test which should just add the Exception Trace to the output of stderr.
     */
    #[Before('moveToPath')]
    public function runTestWithDataProvidersExceptionStderrVerbose(CliGuy $I)
    {
        $I->executeCommand('run -n unit DataProvidersExceptionCest -v 2>&1', false);
        // For Unit tests PHPUnit throws the errors, this confirms that we haven't ended up running PHPUnit test Loader
        $I->dontSeeInShellOutput('There was 1 warning');
        $I->dontSeeInShellOutput('PHPUnit_Framework_Warning');
        $I->dontSeeInShellOutput('The data provider specified for DataProvidersExceptionTest::testIsTriangle');
        $I->dontSeeInShellOutput('FAILURES!');
        $I->dontSeeInShellOutput('WARNINGS!');
        $I->dontSeeInShellOutput('OK');
        // We should not see the messages related to a failure to parse the dataProvider function
        $I->dontSeeInShellOutput('[Codeception\Exception\TestParseException]');
        $I->dontSeeInShellOutput("Couldn't parse test");
        $I->dontSeeInShellOutput("DataProvider 'rectangle' for DataProvidersFailureCest::testIsTriangle is ");

        // We should just see the message
        $I->seeInShellOutput('Something went wrong!!!');
        // We have the verbose flag set, so there should be a trace.
        $I->seeInShellOutput('[Exception]');
        $I->seeInShellOutput('Exception trace:');
        $I->seeResultCodeIs(1);
    }

    #[Before('moveToPath')]
    public function runInvalidDataProvider(CliGuy $I)
    {
        $I->executeCommand('run -n unit InvalidDataProviderTest.php -v 2>&1', false);
        $I->seeInShellOutput('[Exception]');
        $I->seeInShellOutput('Data provider failed');
        $I->dontSeeInShellOutput('Tests: 1');
        $I->dontSeeInShellOutput('Errors: 1');
    }
}
