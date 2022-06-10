<?php

class ErrorExpectationsCest
{
    
    public function _before(\CliGuy $I)
    {
        $I->amInPath('tests/data/error_handling');
    }
    
    public function expectNoticeWorks(\CliGuy $I)
    {
        $this->skipIfOldPhpUnit($I);
        
        $I->executeCommand('run tests/unit/ErrorExceptionTest.php:testNotice');
        $I->seeInShellOutput("OK (");
    }
    
    public function expectWarningWorks(\CliGuy $I)
    {
        $this->skipIfOldPhpUnit($I);
        
        $I->executeCommand('run tests/unit/ErrorExceptionTest.php:testWarning');
        $I->seeInShellOutput('OK (');
    }
    
    public function expectErrorWorks(\CliGuy $I)
    {
        $this->skipIfOldPhpUnit($I);
        
        $I->executeCommand('run tests/unit/ErrorExceptionTest.php:testError');
        $I->seeInShellOutput('OK (');
    }
    
    public function expectDeprecationWorks(\CliGuy $I)
    {
        $this->skipIfOldPhpUnit($I);

        $I->executeCommand('run tests/unit/ErrorExceptionTest.php:testDeprecation');
        $I->seeInShellOutput('OK (');
    }
    
    private function skipIfOldPhpUnit(CliGuy $I)
    {
        if (version_compare(\PHPUnit\Runner\Version::id(), '8.4.0', '<')) {
            $I->markTestSkipped('expectXXX is only available on PHPUnit 8.4+');
        }
    }
}
