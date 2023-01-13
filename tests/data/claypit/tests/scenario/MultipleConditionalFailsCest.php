<?php

class MultipleConditionalFailsCest
{
    public function multipleFails(ScenarioGuy $I)
    {
        $I->canSeeFileFound('not-a-file');
        $I->assertTrue(true);
        $I->assertTrue(true);
        $I->assertTrue(true);
        $I->assertTrue(true);
        $I->assertTrue(true);
        $I->canSeeFileFound('not-a-dir');
        $I->assertFalse(false);
        $I->assertFalse(false);
        $I->assertFalse(false);
        $I->assertFalse(false);
        $I->assertFalse(false);
        $I->canSeeFileFound('nothing');
    }
}
