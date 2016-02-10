<?php

class AngularCest
{
    public function _before(AngularGuy $I)
    {
        $I->amOnPage('/');
    }

    public function followLinks(AngularGuy $I)
    {
        $I->click('Get more info!');
        $I->see('About', 'h1');
        $I->seeInCurrentUrl('#/info');
        $I->expect('Angular scope is rendered');
        $I->see('Welcome to event app', 'p');
        $I->click('Back to form');
        $I->see('Create Event', 'h1');
    }

    public function fillFieldByName(AngularGuy $I)
    {
        $I->see('Create Event', 'h1');
        $I->fillField('Name', 'davert');
        $I->submit();
        $I->dontSee('Please wait');
        $I->see('Thank you');
        $I->see('davert', '#data');
        $I->seeInFormResult(['name' => 'davert']);
    }

    /**
     * @depends fillFieldByName
     * @param AngularGuy $I
     */
    public function fillFieldByPlaceholder(AngularGuy $I)
    {
        $I->fillField('Please enter a name', 'davert');
        $I->submit();
        $I->seeInFormResult(['name' => 'davert']);
    }

    /**
     * @depends fillFieldByName
     * @param AngularGuy $
     */
    public function fillRadioByLabel(AngularGuy $I)
    {
        $I->checkOption('Free');
        $I->submit();
        $I->seeInFormResult(['price' => '0']);
    }

    public function fillInWysiwyg(AngularGuy $I)
    {
        $I->expect('i can edit editable divs');
        $I->fillField('.cke_editable', 'Hello world');
        $I->wait(1);
        $I->submit();
        $I->seeInFormResult(['htmldesc' => "<p>Hello world</p>\n"]);
    }

    public function fillSelect(AngularGuy $I)
    {
        $I->selectOption('Guest Speaker', 'Iron Man');
        $I->submit();
        $I->seeInFormResult(["speaker1" => "iron_man"]);
    }

}