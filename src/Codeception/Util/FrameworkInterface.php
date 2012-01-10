<?php
namespace Codeception\Util;

/**
 * Interface for all Framework connectors.
 * PhpBrowser acts similarly, as universal connectorm thus implements it too.
 *
 */

interface FrameworkInterface
{
    public function amOnPage($page);
    public function see($text, $selector = null);
    public function dontSee($text, $selector = null);
    public function click($link);
    public function submitForm($selector, $params);
    public function sendAjaxPostRequest($uri, $params = array());
    public function sendAjaxGetRequest($uri, $params = array());
    public function seeLink($text, $url = null);
    public function dontSeeLink($text, $url = null);
    public function seeInCurrentUrl($uri);
    public function seeCheckboxIsChecked($checkbox);
    public function dontSeeCheckboxIsChecked($checkbox);
    public function seeInField($field, $value);
    public function dontSeeInField($field, $value);
    public function selectOption($select, $option);
    public function checkOption($option);
    public function uncheckOption($option);
    public function fillField($field, $value);
    public function fillFields(array $fields);
    public function attachFile($field, $filename);
}
