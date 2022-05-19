<?php

class ErrorExpectationsCest
{
    
    public function _before(\CliGuy $I)
    {
        $I->amInPath('tests/data/error_handling');
    }
    
    public function expectNoticeWorks(\CliGuy $I)
    {
        $this->skipIfNot72($I);
        
        $I->executeCommand('run tests/unit/ErrorExceptionTest.php:test_notice');
        $I->seeInShellOutput("OK (");
    }
    
    public function expectWarningWorks(\CliGuy $I)
    {
        $this->skipIfNot72($I);
        
        $I->executeCommand('run tests/unit/ErrorExceptionTest.php:test_warning');
        $I->seeInShellOutput('OK (');
    }
    
    public function expectErrorWorks(\CliGuy $I)
    {
        $this->skipIfNot72($I);
        
        $I->executeCommand('run tests/unit/ErrorExceptionTest.php:test_error');
        $I->seeInShellOutput('OK (');
    }
    
    public function expectDeprecationWorks(\CliGuy $I)
    {
        $this->skipIfNot72($I);
        $I->markTestSkipped('This test is just to reproduce that is doesnt work. It will fail because nothing has been implemented');
        
        $I->executeCommand('run tests/unit/ErrorExceptionTest.php:test_deprecation');
        $I->seeInShellOutput('OK (');
    }
    
    private function skipIfNot72(CliGuy $I)
    {
        if(PHP_VERSION_ID < 70200) {
            $I->markTestSkipped('expectXXX is only available on 7.2+');
        }
    }
    
}

