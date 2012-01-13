<?php
/**
 * Author: davert
 * Date: 13.01.12
 *
 * Class TestsForMink
 * Description:
 *
 */

abstract class TestsForMink extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\Module\PhpBrowser
     */
    protected $module;

    public function testAmOnPage() {

         $this->module->amOnPage('/');
         $this->module->see('Welcome to test app!');

         $this->module->_cleanup();
         $this->module->amOnPage('/info');
         $this->module->see('Information');
     }

     public function testSee() {
         $this->module->amOnPage('/');
         $this->module->see('Welcome to test app!');

         $this->module->amOnPage('/');
         $this->module->see('Welcome to test app!','h1');

         $this->module->amOnPage('/info');
         $this->module->see('valuable','p');
         $this->module->dontSee('Welcome');
         $this->module->dontSee('valuable','h1');
     }

     public function testSeeInCurrentUrl() {
         $this->module->amOnPage('/info');
         $this->module->seeInCurrentUrl('/info');
     }

     public function testSeeLink() {
         $this->module->amOnPage('/');
         $this->module->seeLink('More info');
         $this->module->dontSeeLink('/info');
         $this->module->dontSeeLink('#info');

         $this->module->amOnPage('/info');
         $this->module->seeLink('Back');
     }

     public function testClick() {
         $this->module->amOnPage('/');
         $this->module->click('More info');
         $this->module->seeInCurrentUrl('/info');
     }

     public function testCheckboxByCss() {
         $this->module->amOnPage('/form/checkbox');
         $this->module->checkOption('#checkin');
         $this->module->click('Submit');
         $form = data::get('form');
         $this->assertEquals('agree', $form['terms']);
     }

     public function testChecxboxByLabel() {
         $this->module->amOnPage('/form/checkbox');
         $this->module->checkOption('I Agree');
         $this->module->click('Submit');
         $form = data::get('form');
         $this->assertEquals('agree', $form['terms']);
     }

     public function testSelectByCss() {
         $this->module->amOnPage('/form/select');
         $this->module->selectOption('form select[name=age]','adult');
         $this->module->click('Submit');
         $form = data::get('form');
         $this->assertEquals('adult', $form['age']);
     }

     public function testSelectByLabel() {
         $this->module->amOnPage('/form/select');
         $this->module->selectOption('Select your age','dead');
         $this->module->click('Submit');
         $form = data::get('form');
         $this->assertEquals('dead', $form['age']);
     }

     public function testSelectByLabelAndOptionText() {
         $this->module->amOnPage('/form/select');
         $this->module->selectOption('Select your age','21-60');
         $this->module->click('Submit');
         $form = data::get('form');
         $this->assertEquals('adult', $form['age']);
     }

     public function testHidden() {
         $this->module->amOnPage('/form/hidden');
         $this->module->click('Submit');
         $form = data::get('form');
         $this->assertEquals('kill_people', $form['action']);
     }

     public function testTextareaByCss() {
         $this->module->amOnPage('/form/textarea');
         $this->module->fillField('textarea','Nothing special');
         $this->module->click('Submit');
         $form = data::get('form');
         $this->assertEquals('Nothing special', $form['description']);
     }

     public function testTextareaByLabel() {
         $this->module->amOnPage('/form/textarea');
         $this->module->fillField('Description','Nothing special');
         $this->module->click('Submit');
         $form = data::get('form');
         $this->assertEquals('Nothing special', $form['description']);
     }

     public function testTextFieldByCss() {
         $this->module->amOnPage('/form/field');
         $this->module->fillField('#name','Nothing special');
         $this->module->click('Submit');
         $form = data::get('form');
         $this->assertEquals('Nothing special', $form['name']);
     }

     public function testTextFieldByLabel() {
         $this->module->amOnPage('/form/field');
         $this->module->fillField('Name','Nothing special');
         $this->module->click('Submit');
         $form = data::get('form');
         $this->assertEquals('Nothing special', $form['name']);
     }

     public function testFileFieldByCss() {
         $this->module->amOnPage('/form/file');
         $this->module->attachFile('#avatar', 'app/avatar.jpg');
         $this->module->click('Submit');
         $this->assertNotEmpty(data::get('files'));
         $files = data::get('files');
         $this->assertArrayHasKey('avatar', $files);
         $this->assertEquals('avatar.jpg', $files['avatar']['name']);
     }

     public function testFileFieldByLabel() {
         $this->module->amOnPage('/form/file');
         $this->module->attachFile('Avatar', 'app/avatar.jpg');
         $this->module->click('Submit');
         $this->assertNotEmpty(data::get('files'));
     }

     public function testSeeCheckboxIsNotChecked() {
         $this->module->amOnPage('/form/checkbox');
         $this->module->dontSeeCheckboxIsChecked('#checkin');
     }

     public function testSeeCheckboxChecked() {
         $this->module->_initialize();
         $this->module->amOnPage('/info');
         $this->module->seeCheckboxIsChecked('input[type=checkbox]');
     }

}
